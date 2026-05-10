<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Appeal;
use App\Models\MovieView;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    public function index() {
        // Eager load everything to see the reporter, the critique, and the critique's author
        $reports = Report::with(['user', 'review.user'])->latest()->get();
        return view('admin.reports', compact('reports'));
    }

    public function banUser(Request $request, User $user) {
        // Use direct assignment to bypass mass-assignment restrictions on User model
        $user->is_banned = true;
        $user->remember_token = null;
        $user->save();
        // Optionally delete all their reviews too
        $user->reviews()->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User has been banned.']);
        }

        return back()->with('success', 'User has been banned.');
    }

    public function dismiss(Request $request, Report $report) {
        // Remove the report after reviewing it
        $report->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Report dismissed.']);
        }

        return back()->with('success', 'Report dismissed.');
    }

    public function unbanUser(Request $request, User $user) {
        // Use direct assignment to ensure attributes are persisted even if not fillable
        $user->is_banned = false;
        $user->session_token = null;
        $user->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User has been unbanned.']);
        }

        return back()->with('success', 'User has been unbanned.');
    }

    // Appeals management
    public function appealsIndex()
    {
        $appeals = Appeal::with('user')->latest()->get();
        return view('admin.appeals', compact('appeals'));
    }

    public function acceptAppeal(Request $request, Appeal $appeal)
    {
        $user = $appeal->user;
        if ($user) {
            $user->is_banned = false;
            $user->session_token = null;
            $user->save();
        }
        $appeal->status = 'accepted';
        $appeal->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Appeal accepted; user unbanned.']);
        }

        return back()->with('success', 'Appeal accepted.');
    }

    public function denyAppeal(Request $request, Appeal $appeal)
    {
        $appeal->status = 'denied';
        $appeal->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Appeal denied.']);
        }

        return back()->with('success', 'Appeal denied.');
    }

    public function moviesStats(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        // Default to last 30 days when no range provided to stabilize charts
        if (!$start || !$end) {
            $endDt = \Carbon\Carbon::today();
            $startDt = (clone $endDt)->subDays(30);
            $start = $start ?? $startDt->toDateString();
            $end = $end ?? $endDt->toDateString();
        }
        $perPage = 10;

        // Most viewed (from movie_views table)
        $mvQuery = MovieView::query();
        if ($start && $end) {
            $mvQuery->whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"]);
        }
        $mostViewedCollection = $mvQuery->orderBy('views', 'desc')->get();

        // Ensure MovieView records expose a movie_title when possible by falling back
        // to any existing review record for that movie id. This makes chart labels
        // and the list show titles instead of raw ids when available.
        $mostViewedCollection = $mostViewedCollection->map(function($m){
            if (empty($m->movie_title)) {
                $title = Review::where('movie_id', $m->movie_id)->value('movie_title');
                $m->movie_title = $title ?: null;
            }
            return $m;
        });

        // Aggregate review stats per movie (top-level reviews only)
        $rQuery = Review::whereNull('parent_id');
        if ($start && $end) {
            $rQuery->whereBetween('created_at', ["$start 00:00:00", "$end 23:59:59"]);
        }

        $reviewAgg = $rQuery->select(
            'movie_id',
            'movie_title',
            DB::raw('COUNT(*) as reviews_count'),
            DB::raw('AVG(COALESCE(rating,0)) as avg_rating'),
            DB::raw('SUM(COALESCE(upvotes,0) + COALESCE(downvotes,0)) as votes_sum')
        )
        ->groupBy('movie_id','movie_title')
        ->get();

        // Prepare collections for different leaderboards
        $criticsChoiceCollection = $reviewAgg->filter(function($r){ return $r->reviews_count > 0; })->sortByDesc('avg_rating')->values();
        $mostCritiquedCollection = $reviewAgg->sortByDesc('reviews_count')->values();
        $mostEngagingCollection = $reviewAgg->map(function($r){ $r->engagement_score = ($r->reviews_count + $r->votes_sum); return $r; })->sortByDesc('engagement_score')->values();

        // Paginate each collection
        $mostViewed = $this->paginateCollection($mostViewedCollection, $perPage, $request->input('mv_page', 1), url()->current(), $request->query());
        $criticsChoice = $this->paginateCollection($criticsChoiceCollection, $perPage, $request->input('cc_page', 1), url()->current(), $request->query());
        $mostCritiqued = $this->paginateCollection($mostCritiquedCollection, $perPage, $request->input('mc_page', 1), url()->current(), $request->query());
        $mostEngaging = $this->paginateCollection($mostEngagingCollection, $perPage, $request->input('me_page', 1), url()->current(), $request->query());

        // Prepare small datasets for charts (top 10 overall, ignoring pagination)
        // Ensure each item exposes a `label` property (movie title when available, otherwise movie id)
        $chartMostViewed = $mostViewedCollection->take(20)->map(function($m){
            $label = trim((string)($m->movie_title ?? '')) ?: (string)($m->movie_id ?? '');
            $m->label = $label;
            return $m;
        });

        $chartCritics = $criticsChoiceCollection->take(20)->map(function($m){
            $label = trim((string)($m->movie_title ?? '')) ?: (string)($m->movie_id ?? '');
            $m->label = $label;
            return $m;
        });

        $chartCritiqued = $mostCritiquedCollection->take(20)->map(function($m){
            $label = trim((string)($m->movie_title ?? '')) ?: (string)($m->movie_id ?? '');
            $m->label = $label;
            return $m;
        });

        $chartEngaging = $mostEngagingCollection->take(20)->map(function($m){
            $label = trim((string)($m->movie_title ?? '')) ?: (string)($m->movie_id ?? '');
            $m->label = $label;
            return $m;
        });

        return view('admin.stats', compact(
            'mostViewed','criticsChoice','mostCritiqued','mostEngaging',
            'chartMostViewed','chartCritics','chartCritiqued','chartEngaging','start','end'
        ));
    }

    protected function paginateCollection(Collection $items, $perPage, $page = 1, $path = null, $query = [])
    {
        $page = max(1, (int) $page);
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => $path,
            'query' => $query,
        ]);
    }
}
