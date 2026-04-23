@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <h2 class="fw-bold text-body-emphasis mb-0">
            {{ request('query') ? 'Search Results for "'.request('query').'"' : 'Trending Movies' }}
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
                            
                            {{-- NATIVE RATING BLOCK --}}
                            <div class="position-absolute top-0 end-0 m-2">
                                @php
                                    $localAvg = \App\Models\Review::where('movie_id', $movie['id'])->avg('rating');
                                @endphp
                                
                                @if($localAvg)
                                    <span class="badge bg-primary backdrop-blur border border-primary-subtle shadow-sm">
                                        <i class="fas fa-user-edit me-1"></i> {{ number_format($localAvg, 1) }}
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