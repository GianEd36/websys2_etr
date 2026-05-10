@extends('layouts.app')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">Movies Statistics</h2>
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-light">Reports</a>
            <a href="{{ route('admin.appeals.index') }}" class="btn btn-sm btn-outline-light">Appeals</a>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $start ?? '' }}">
        </div>
        <div class="col-auto">
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $end ?? '' }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-primary">Apply</button>
        </div>
    </form>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="text-white mb-0">Most Viewed</h5>
                        <div class="ms-2 d-flex">
                            <select class="form-select form-select-sm chart-type-selector me-2" data-chart="chartMostViewed">
                                <option value="bar" selected>Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                            </select>
                            <select class="form-select form-select-sm chart-limit-selector" data-chart="chartMostViewed">
                                <option value="5">Top 5</option>
                                <option value="10" selected>Top 10</option>
                                <option value="20">Top 20</option>
                            </select>
                        </div>
                    </div>
                    <div style="min-height:260px;">
                        <canvas id="chartMostViewed" height="200"></canvas>
                    </div>
                    <ul class="list-group list-group-flush mt-3">
                        @forelse($mostViewed as $m)
                            <li class="list-group-item bg-transparent border-secondary d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $m->movie_title ? $m->movie_title : $m->movie_id }}</strong>
                                    <div class="small text-muted">ID: {{ $m->movie_id }}</div>
                                </div>
                                <span class="badge bg-primary">{{ $m->views }}</span>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent border-secondary">No data</li>
                        @endforelse
                    </ul>
                    <div class="mt-2">{!! $mostViewed->appends(request()->query())->links('pagination::bootstrap-5') !!}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="text-white mb-0">Critic's Choice (Top Avg Rating)</h5>
                        <div class="ms-2 d-flex">
                            <select class="form-select form-select-sm chart-type-selector me-2" data-chart="chartCritics">
                                <option value="bar" selected>Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                            </select>
                            <select class="form-select form-select-sm chart-limit-selector" data-chart="chartCritics">
                                <option value="5">Top 5</option>
                                <option value="10" selected>Top 10</option>
                                <option value="20">Top 20</option>
                            </select>
                        </div>
                    </div>
                    <div style="min-height:260px;">
                        <canvas id="chartCritics" height="200"></canvas>
                    </div>
                    <ul class="list-group list-group-flush mt-3">
                        @forelse($criticsChoice as $c)
                            <li class="list-group-item bg-transparent border-secondary d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $c->movie_title }}</strong>
                                    <div class="small text-muted">ID: {{ $c->movie_id }}</div>
                                </div>
                                <span class="badge bg-warning text-dark">{{ number_format($c->avg_rating, 2) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent border-secondary">No data</li>
                        @endforelse
                    </ul>
                    <div class="mt-2">{!! $criticsChoice->appends(request()->query())->links('pagination::bootstrap-5') !!}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="text-white mb-0">Most Critiqued</h5>
                        <div class="ms-2 d-flex">
                            <select class="form-select form-select-sm chart-type-selector me-2" data-chart="chartCritiqued">
                                <option value="bar" selected>Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                            </select>
                            <select class="form-select form-select-sm chart-limit-selector" data-chart="chartCritiqued">
                                <option value="5">Top 5</option>
                                <option value="10" selected>Top 10</option>
                                <option value="20">Top 20</option>
                            </select>
                        </div>
                    </div>
                    <div style="min-height:260px;">
                        <canvas id="chartCritiqued" height="200"></canvas>
                    </div>
                    <ul class="list-group list-group-flush mt-3">
                        @forelse($mostCritiqued as $m)
                            <li class="list-group-item bg-transparent border-secondary d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $m->movie_title }}</strong>
                                    <div class="small text-muted">ID: {{ $m->movie_id }}</div>
                                </div>
                                <span class="badge bg-secondary">{{ $m->reviews_count }}</span>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent border-secondary">No data</li>
                        @endforelse
                    </ul>
                    <div class="mt-2">{!! $mostCritiqued->appends(request()->query())->links('pagination::bootstrap-5') !!}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="text-white mb-0">Most Engaging</h5>
                        <div class="ms-2 d-flex">
                            <select class="form-select form-select-sm chart-type-selector me-2" data-chart="chartEngaging">
                                <option value="bar" selected>Bar</option>
                                <option value="line">Line</option>
                                <option value="pie">Pie</option>
                            </select>
                            <select class="form-select form-select-sm chart-limit-selector" data-chart="chartEngaging">
                                <option value="5">Top 5</option>
                                <option value="10" selected>Top 10</option>
                                <option value="20">Top 20</option>
                            </select>
                        </div>
                    </div>
                    <div style="min-height:260px;">
                        <canvas id="chartEngaging" height="200"></canvas>
                    </div>
                    <ul class="list-group list-group-flush mt-3">
                        @forelse($mostEngaging as $e)
                            <li class="list-group-item bg-transparent border-secondary d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $e->movie_title }}</strong>
                                    <div class="small text-muted">ID: {{ $e->movie_id }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">Engagement: {{ $e->engagement_score ?? ($e->reviews_count + $e->votes_sum) }}</div>
                                    <span class="badge bg-info">{{ $e->reviews_count }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent border-secondary">No data</li>
                        @endforelse
                    </ul>
                    <div class="mt-2">{!! $mostEngaging->appends(request()->query())->links('pagination::bootstrap-5') !!}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartData = {
    mostViewed: {
        labels: {!! json_encode($chartMostViewed->pluck('label')->values()->all()) !!},
        data: {!! json_encode($chartMostViewed->pluck('views')->values()->all()) !!}
    },
    critics: {
        labels: {!! json_encode($chartCritics->pluck('label')->values()->all()) !!},
        data: {!! json_encode($chartCritics->pluck('avg_rating')->map(function($v){ return round($v,2); })->values()->all()) !!}
    },
    critiqued: {
        labels: {!! json_encode($chartCritiqued->pluck('label')->values()->all()) !!},
        data: {!! json_encode($chartCritiqued->pluck('reviews_count')->values()->all()) !!}
    },
    engaging: {
        labels: {!! json_encode($chartEngaging->pluck('label')->values()->all()) !!},
        data: {!! json_encode($chartEngaging->pluck('engagement_score')->values()->all()) !!}
    }
};

// Chart instances store
const charts = {};

function generateColors(n, baseColor) {
    const palette = [
        'rgba(54,162,235,0.7)','rgba(255,99,132,0.7)','rgba(255,205,86,0.7)',
        'rgba(153,102,255,0.7)','rgba(75,192,192,0.7)','rgba(201,203,207,0.7)',
        'rgba(255,159,64,0.7)','rgba(99,255,132,0.7)','rgba(132,99,255,0.7)','rgba(86,205,255,0.7)'
    ];
    const out = [];
    for (let i=0;i<n;i++) out.push(palette[i % palette.length]);
    return out;
}

function createChart(id, type, labels, data, labelText, color, maxPoints = 10){
    // Normalize and trim according to requested maxPoints
    labels = Array.isArray(labels) ? labels.map(String) : [];
    data = Array.isArray(data) ? data.map(v => { const n = Number(v); return Number.isFinite(n) ? n : 0; }) : [];
    const len = Math.min(labels.length, data.length, maxPoints);
    labels = labels.slice(0, len);
    data = data.slice(0, len);

    // Destroy previous
    if (charts[id]) {
        try { charts[id].destroy(); } catch(e){}
        delete charts[id];
    }

    const ctx = document.getElementById(id).getContext('2d');

    if (type === 'pie') {
        const bg = generateColors(data.length, color);
        charts[id] = new Chart(ctx, {
            type: 'pie',
            data: { labels, datasets: [{ data, backgroundColor: bg }] },
            options: { responsive:true, maintainAspectRatio:false, plugins: { legend: { position: 'bottom' } } }
        });
        return;
    }

    const dataset = {
        label: labelText,
        data,
        backgroundColor: Array.isArray(color) ? color : color,
        borderColor: (type === 'line') ? (Array.isArray(color) ? color[0] : color) : undefined,
        fill: false,
    };

    charts[id] = new Chart(ctx, {
        type: type,
        data: { labels, datasets: [dataset] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true },
                x: { ticks: { maxRotation: 45, minRotation: 0 } }
            },
            plugins: { legend: { display: true } }
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    // Initial render (bar defaults)
    // Initial render (respecting selected limits if present)
    const initialLimit = (selId) => {
        const el = document.querySelector(`.chart-limit-selector[data-chart="${selId}"]`);
        return el ? Math.max(1, Number(el.value) || 10) : 10;
    };

    if (chartData.mostViewed.labels.length) createChart('chartMostViewed','bar', chartData.mostViewed.labels, chartData.mostViewed.data, 'Views', 'rgba(54,162,235,0.7)', initialLimit('chartMostViewed'));
    if (chartData.critics.labels.length) createChart('chartCritics','bar', chartData.critics.labels, chartData.critics.data, 'Avg Rating', 'rgba(255,205,86,0.7)', initialLimit('chartCritics'));
    if (chartData.critiqued.labels.length) createChart('chartCritiqued','bar', chartData.critiqued.labels, chartData.critiqued.data, 'Reviews', 'rgba(153,102,255,0.7)', initialLimit('chartCritiqued'));
    if (chartData.engaging.labels.length) createChart('chartEngaging','bar', chartData.engaging.labels, chartData.engaging.data, 'Engagement', 'rgba(75,192,192,0.7)', initialLimit('chartEngaging'));

    // Wire selectors to toggle types
    // Wire type selectors
    document.querySelectorAll('.chart-type-selector').forEach(sel => {
        sel.addEventListener('change', function(){
            const chartId = this.dataset.chart;
            const type = this.value;
            const limit = initialLimit(chartId);
            if (chartId === 'chartMostViewed') createChart(chartId, type, chartData.mostViewed.labels, chartData.mostViewed.data, 'Views', 'rgba(54,162,235,0.7)', limit);
            if (chartId === 'chartCritics') createChart(chartId, type, chartData.critics.labels, chartData.critics.data, 'Avg Rating', 'rgba(255,205,86,0.7)', limit);
            if (chartId === 'chartCritiqued') createChart(chartId, type, chartData.critiqued.labels, chartData.critiqued.data, 'Reviews', 'rgba(153,102,255,0.7)', limit);
            if (chartId === 'chartEngaging') createChart(chartId, type, chartData.engaging.labels, chartData.engaging.data, 'Engagement', 'rgba(75,192,192,0.7)', limit);
        });
    });

    // Wire limit selectors
    document.querySelectorAll('.chart-limit-selector').forEach(sel => {
        sel.addEventListener('change', function(){
            const chartId = this.dataset.chart;
            const limit = Math.max(1, Number(this.value) || 10);
            const typeEl = document.querySelector(`.chart-type-selector[data-chart="${chartId}"]`);
            const type = typeEl ? typeEl.value : 'bar';
            if (chartId === 'chartMostViewed') createChart(chartId, type, chartData.mostViewed.labels, chartData.mostViewed.data, 'Views', 'rgba(54,162,235,0.7)', limit);
            if (chartId === 'chartCritics') createChart(chartId, type, chartData.critics.labels, chartData.critics.data, 'Avg Rating', 'rgba(255,205,86,0.7)', limit);
            if (chartId === 'chartCritiqued') createChart(chartId, type, chartData.critiqued.labels, chartData.critiqued.data, 'Reviews', 'rgba(153,102,255,0.7)', limit);
            if (chartId === 'chartEngaging') createChart(chartId, type, chartData.engaging.labels, chartData.engaging.data, 'Engagement', 'rgba(75,192,192,0.7)', limit);
            // Optionally trim the visible list under the chart if it exists
            const list = document.querySelector(`#${chartId}`).closest('.card-body').querySelector('.list-group');
            if (list) {
                const items = list.querySelectorAll('li');
                items.forEach((li, idx) => { li.style.display = (idx < limit) ? '' : 'none'; });
            }
        });
    });
});
</script>
@endpush
