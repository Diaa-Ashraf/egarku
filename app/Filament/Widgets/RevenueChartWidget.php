<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md'      => 1,
        'xl'      => 1,
    ];

    public function getHeading(): ?string
    {
        return 'مخطط الإيرادات لآخر 6 أشهر (ج.م)';
    }

    protected function getData(): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();

        $totals = Transaction::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->year, $row->month));

        $months = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $months[] = $date->translatedFormat('F Y');
            $data[] = (float) ($totals[$key]->total ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label'           => 'إجمالي الإيرادات (ج.م)',
                    'data'            => $data,
                    'borderColor'     => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.12)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
