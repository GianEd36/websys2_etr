<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MovieView;
use App\Models\Review;
use Illuminate\Support\Facades\Http;

class BackfillMovieTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backfill:movie-titles {--api : Use TMDB API to fetch titles when reviews are not available} {--limit=0 : Limit how many records to process (0 = all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing movie_title values on movie_views (from reviews or TMDB API)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $useApi = $this->option('api');
        $limit = (int) $this->option('limit');

        $query = MovieView::whereNull('movie_title');
        if ($limit > 0) $query->limit($limit);

        $total = $query->count();
        if ($total === 0) {
            $this->info('No movie_views rows missing movie_title.');
            return 0;
        }

        $this->info("Found {$total} movie_views missing titles. Starting backfill...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $skipped = 0;

        $rows = $query->get();
        foreach ($rows as $row) {
            $title = Review::where('movie_id', $row->movie_id)->value('movie_title');
            if ($title) {
                $row->movie_title = $title;
                $row->save();
                $updated++;
                $bar->advance();
                continue;
            }

            if ($useApi) {
                try {
                    $token = config('services.tmdb.token');
                    if ($token) {
                        $resp = Http::withToken($token)->get("https://api.themoviedb.org/3/movie/{$row->movie_id}");
                        if ($resp->ok()) {
                            $data = $resp->json();
                            $title = $data['title'] ?? ($data['name'] ?? null);
                            if ($title) {
                                $row->movie_title = $title;
                                $row->save();
                                $updated++;
                                $bar->advance();
                                continue;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // swallow individual errors, continue
                }
            }

            $skipped++;
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->info("Backfill complete: {$updated} updated, {$skipped} skipped (no title found).");

        return 0;
    }
}
