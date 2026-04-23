<!DOCTYPE html>
<html lang="en" data-bs-theme="dark"> {{-- Default to dark --}}
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KritikIt: CINEMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Smooth transition when switching themes */
        body { transition: background-color 0.3s, color 0.3s; }
        .movie-card img { transition: transform 0.2s; }
        .movie-card:hover img { transform: scale(1.03); }
        /* This ensures links behave themselves in both modes */
        a { color: inherit; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg border-bottom shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-body-emphasis" href="{{ route('home') }}">KritikIt: CINEMA</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    {{-- Genre Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-body-emphasis" href="#" role="button" data-bs-toggle="dropdown">
                            Genres
                        </a>
                        <ul class="dropdown-menu shadow">
                            @foreach($genres as $genre)
                                <li>
                                    <a class="dropdown-item" href="{{ url('/genre/'.$genre['id']) }}">
                                        {{ $genre['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </ul>

                {{-- Search Bar --}}
                <form class="d-flex mx-lg-4 my-2 my-lg-0" action="{{ url('/search') }}" method="GET">
                    <div class="input-group">
                        <input class="form-control form-control-sm bg-body-tertiary border-secondary-subtle" 
                            type="search" 
                            name="query" 
                            placeholder="Search movies..." 
                            aria-label="Search">
                        <button class="btn btn-outline-primary btn-sm" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <div class="d-flex align-items-center">
                    {{-- Theme Toggle --}}
                    <button class="btn btn-link nav-link me-3" id="themeToggle">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>

                    @guest
                        <a class="nav-link me-2 text-body-emphasis" href="{{ route('login') }}">Login</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Join</a>
                    @else
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-body-emphasis" href="#" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">My Reviews</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <main class="container">
        @yield('content')
    </main>

    <script>
        const btn = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');
        const html = document.documentElement;

        btn.addEventListener('click', () => {
            if (html.getAttribute('data-bs-theme') === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                icon.classList.replace('fa-moon', 'fa-sun');
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                icon.classList.replace('fa-sun', 'fa-moon');
            }
        });
    </script>
</body>
</html>