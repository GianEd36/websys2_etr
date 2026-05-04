@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <h2 class="fw-bold text-body-emphasis mb-0">
            @if(request('query'))
                Search Results for "{{ request('query') }}"
            @elseif(request('sort_by') === 'views')
                <i class="fas fa-eye text-primary me-2"></i> Most Viewed
            @elseif(request('sort_by') === 'top_rated')
                <i class="fas fa-star text-warning me-2"></i> Critics' Choice (Highest Rated)
            @elseif(request('sort_by') === 'critiqued')
                <i class="fas fa-pen-nib text-warning me-2"></i> Most Critiqued
            @elseif(request('sort_by') === 'engaged')
                <i class="fas fa-comments text-success me-2"></i> Most Engaged
            @else
                Trending Movies
            @endif
        </h2>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
        @foreach($movies as $movie)
            <div class="col">
                <div class="card h-100 shadow-sm border-0 movie-card overflow-hidden">
                    <a href="{{ url('/movie/'.$movie['id']) }}" class="text-decoration-none link-body-emphasis">
                        <div class="position-relative">
                            <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" 
                                 class="card-img-top" 
                                 alt="{{ $movie['title'] }}"
                                 style="aspect-ratio: 2/3; object-fit: cover;">
                            
                            {{-- NATIVE RATING & SOCIAL BLOCK --}}
                            <div class="position-absolute top-0 end-0 m-2 d-flex flex-column align-items-end gap-1">
                                {{-- 1. Rating Badge (Existing) --}}
                                @php
                                    $localAvg = \App\Models\Review::where('movie_id', $movie['id'])->whereNull('parent_id')->avg('rating');
                                @endphp
                                @if($localAvg)
                                    <span class="badge bg-primary backdrop-blur border border-primary-subtle shadow-sm">
                                        <i class="fas fa-star me-1 text-warning"></i> {{ number_format($localAvg, 1) }}
                                    </span>
                                @endif

                                {{-- 2. Critique Count Badge (New!) --}}
                                @php
                                    $critiqueCount = \App\Models\Review::where('movie_id', $movie['id'])->whereNull('parent_id')->count();
                                @endphp
                                @if($critiqueCount > 0)
                                    <span class="badge bg-warning text-dark backdrop-blur border border-warning-subtle shadow-sm">
                                        <i class="fas fa-pen-nib me-1"></i> {{ $critiqueCount }}
                                    </span>
                                @endif

                                {{-- 3. Engagement/Reply Badge (New!) --}}
                                @php
                                    $engagementCount = \App\Models\Review::where('movie_id', $movie['id'])->whereNotNull('parent_id')->count();
                                @endphp
                                @if($engagementCount > 0)
                                    <span class="badge bg-success backdrop-blur border border-success-subtle shadow-sm">
                                        <i class="fas fa-comments me-1"></i> {{ $engagementCount }}
                                    </span>
                                @endif
                            </div>

                            {{-- 4. View Count Badge (Bottom-Left) --}}
                            <div class="position-absolute bottom-0 start-0 m-2">
                                @php
                                    $views = \App\Models\MovieView::where('movie_id', $movie['id'])->value('views') ?? 0;
                                @endphp
                                @if($views > 0)
                                    <span class="badge bg-dark backdrop-blur border border-secondary-subtle shadow-sm opacity-75">
                                        <i class="fas fa-eye me-1 text-info"></i> {{ number_format($views) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold text-truncate mb-1">{{ $movie['title'] }}</h6>
                            <p class="card-text small text-secondary mb-0">
                                {{ \Carbon\Carbon::parse($movie['release_date'] ?? '')->format('Y') }}
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="d-flex flex-column align-items-center mt-5">
        <nav>
            <ul class="pagination pagination-lg mb-2">
                <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                    <a class="page-link shadow-sm border-0 rounded-start-pill px-4" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}">
                        <i class="fas fa-chevron-left me-2"></i>Prev
                    </a>
                </li>
                <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                    <a class="page-link shadow-sm border-0 rounded-end-pill px-4" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}">
                        Next<i class="fas fa-chevron-right ms-2"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<style>
    .movie-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .movie-card:hover { transform: translateY(-8px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; }
    .backdrop-blur { backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
</style>
@endsection