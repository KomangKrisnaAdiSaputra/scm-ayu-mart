@extends('layouts.app')
@section('titlePage', 'Dashboard')

@section('app')
    {{-- Cards --}}
    @foreach (collect($cards ?? [])->chunk(6) as $batchCards)
        <div class="row">
            @foreach ($batchCards as $key => $item)
                @php
                    $cardLg = $batchCards->count() <= 4 ? 3 : 12 / $batchCards->count();
                    $cardMd = $batchCards->count() <= 4 ? 6 : 12 / ($batchCards->count() / 2);
                    $cardSm = $batchCards->count() <= 4 ? 12 : 12 / ($batchCards->count() / 2 / 2);
                @endphp
                <div class="col-lg-{{ $cardLg }} col-md-{{ $cardMd }} col-sm-{{ $cardSm }}"
                    {{ isset($item['hidden']) ? 'hidden' : '' }}>
                    <div class="card card-statistic-1">
                        <div class="card-icon {{ $item['bg'] }}"><i class="fas fa-{{ $item['icon'] }}"></i></div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>{{ $item['header'] }}</h4>
                            </div>
                            <div class="card-body">{{ $item['body'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
    {{-- End Cards --}}

    {{-- Diagram --}}
    @foreach (collect($charts ?? [])->chunk(4) as $batchCharts)
        <div class="row">
            @foreach ($batchCharts as $chart)
                @php
                    $colChartLg =
                        $batchCharts->count() <= 4 ? ($batchCharts->count() == 2 ? 6 : 3) : 12 / $batchCharts->count();
                    $colChartMd =
                        $batchCharts->count() <= 4
                            ? ($batchCharts->count() == 2
                                ? 12
                                : 6)
                            : 12 / ($batchCharts->count() / 2);
                    $colChartSm = $batchCharts->count() <= 4 ? 12 : 12 / ($batchCharts->count() / 2 / 2);
                @endphp

                <div class="col-lg-{{ $colChartLg }} col-md-{{ $colChartMd }} col-sm-{{ $colChartSm }}">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ $chart['title'] }}</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="{{ $chart['id'] }}"></canvas>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
    {{-- End Diagram --}}

    {{-- Tables --}}
    <div class="row">
        @foreach ($tables ?? [] as $table)
            <div class="col-lg-{{ $table['col'] ?? 12 }}">
                <div class="card border-{{ $table['theme'] }}">
                    <div class="card-header bg-{{ $table['theme'] }} text-white">
                        <i class="fas fa-{{ $table['icon'] }}"></i> {{ $table['title'] }}
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
                                                @if (is_array($cell) && ($cell['type'] ?? null) === 'badge')
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
                                        <td colspan="{{ count($table['headers']) }}" class="text-center text-success py-3">
                                            {{ $table['empty_text'] }}
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
    {{-- End Tables --}}

@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        const charts = @json($charts ?? []);

        charts.forEach(chart => {
            const ctx = document.getElementById(chart.id);
            if (!ctx) return;

            new Chart(ctx, {
                type: chart.type,
                data: chart.data,
                options: chart.options ?? {}
            });
        });
    </script>
@endsection
