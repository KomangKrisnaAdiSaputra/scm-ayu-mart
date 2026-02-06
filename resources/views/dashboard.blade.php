@extends('layouts.app')
@section('titlePage', 'Dashboard')

@section('app')

    @foreach ($sections as $section)
        {{-- ================= TITLE ================= --}}
        @if ($section['type'] === 'title')
            <div class="row my-4">
                <div class="col-12">
                    <h3 class="font-weight-bold">{{ $section['title'] }}</h3>
                </div>
            </div>
        @endif

        {{-- ================= CARDS ================= --}}
        @if ($section['type'] === 'cards')
            @php $col = 12 / ($section['per_row'] ?? 4); @endphp
            <div class="row">
                @foreach ($section['items'] as $item)
                    <div class="col-lg-{{ $col }} col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon {{ $item['bg'] }}">
                                <i class="fas fa-{{ $item['icon'] }}"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>{{ $item['header'] }}</h4>
                                </div>
                                <div class="card-body">
                                    {{ $item['body'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ================= DONUT (ONE CARD, LIST BELOW) ================= --}}
        @if ($section['type'] === 'donut')
            @php $col = 12 / ($section['per_row'] ?? 2); @endphp

            <div class="row">
                @foreach ($section['items'] as $item)
                    @if (count($item['details']) > 0)
                        <div class="col-lg-{{ $col }} col-md-12">
                            <div class="card">

                                {{-- HEADER --}}
                                <div class="card-header">
                                    <h4>{{ $item['title'] }}</h4>
                                </div>

                                {{-- BODY --}}
                                <div class="card-body">

                                    {{-- DONUT --}}
                                    <div class="text-center mb-4">
                                        <canvas id="{{ $item['id'] }}" style="max-height:220px"></canvas>
                                    </div>

                                    {{-- DETAIL LIST --}}
                                    <ul class="list-group list-group-flush">
                                        @foreach ($item['details'] as $d)
                                            <li
                                                class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                <span>
                                                    <i class="fas fa-{{ $d['icon'] }} me-2"
                                                        style="color: {{ $d['color'] }}"></i>
                                                    {{ $d['label'] }}
                                                </span>
                                                <span class="badge"
                                                    style="background-color: {{ $d['color'] }}; color: #fff;">
                                                    {{ $d['value'] }}
                                                </span>

                                            </li>
                                        @endforeach
                                    </ul>


                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif


        {{-- ================= CHARTS ================= --}}
        @if ($section['type'] === 'charts')
            @php $col = 12 / ($section['per_row'] ?? 2); @endphp
            <div class="row">
                @foreach ($section['items'] as $chart)
                    @if (count($chart['data']['datasets']) > 0)
                        <div class="col-lg-{{ $col }} col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>{{ $chart['title'] }}</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="{{ $chart['id'] }}"></canvas>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- ================= TABLES ================= --}}
        @if ($section['type'] === 'tables')
            @php $col = 12 / ($section['per_row'] ?? 1); @endphp
            <div class="row">
                @foreach ($section['items'] as $table)
                    <div class="col-lg-{{ $col }}">
                        <div class="card border-{{ $table['theme'] }}">
                            <div class="card-header bg-{{ $table['theme'] }} text-white">
                                <i class="fas fa-{{ $table['icon'] ?? '' }}"></i>
                                {{ $table['title'] }}
                            </div>
                            <div class="card-body p-0">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            @foreach ($table['headers'] as $header)
                                                <th>{{ $header['label'] }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($table['rows'] as $row)
                                            <tr>
                                                @foreach ($table['headers'] as $header)
                                                    @php $cell = $row[$header['key']] ?? '-' @endphp
                                                    <td>
                                                        @if (is_array($cell) && ($cell['type'] ?? '') === 'badge')
                                                            <span class="badge {{ $cell['class'] }}">
                                                                {{ $cell['text'] }}
                                                            </span>
                                                        @else
                                                            {{ $cell }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($table['headers']) }}"
                                                    class="text-center py-3 text-success">
                                                    {{ $table['empty_text'] ?? 'Data Empty' }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>

    <script>
        const sections = @json($sections);

        sections.forEach(section => {

            // BAR / LINE / DLL
            if (section.type === 'charts') {
                section.items.forEach(chart => {
                    const ctx = document.getElementById(chart.id);
                    if (!ctx) return;

                    new Chart(ctx, {
                        type: chart.type,
                        data: chart.data,
                        options: chart.options ?? {}
                    });
                });
            }

            // DONUT
            if (section.type === 'donut') {
                section.items.forEach(item => {
                    const ctx = document.getElementById(item.id);
                    if (!ctx) return;

                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: item.chart.labels,
                            datasets: [{
                                data: item.chart.data,
                                backgroundColor: item.chart.colors,
                                // backgroundColor: [
                                //     '#6777ef', // primary
                                //     '#3abaf4', // info
                                //     '#63ed7a', // success
                                //     '#ffa426',
                                //     '#fc544b',
                                // ],
                            }],
                        },
                        options: {
                            responsive: true,
                            cutout: '65%',
                            plugins: {
                                legend: {
                                    display: false
                                },
                            },
                        },
                    });
                });
            }

        });
    </script>

@endsection
