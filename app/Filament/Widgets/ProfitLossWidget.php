<?php

namespace App\Filament\Widgets;

use App\Models\ProfitLoss;
use Filament\Support\Enums\IconPosition;
use App\Filament\Resources\ProfitLossResource;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ProfitLossWidget extends BaseWidget
{
  protected static ?string $pollingInterval = null;

  protected static ?int $sort = -2;

  public function getColumns(): int
  {
    return 2;
  }

  protected function getStats(): array
  {
    return [
      Stat::make('Average Net Sales (' . today()->translatedFormat('F Y') . ')', idr(ProfitLoss::getAverageNetSalesForCurrentMonth()))
        ->description('Berdasarkan laporan Profit & Loss')
        ->descriptionIcon(ProfitLossResource::getNavigationIcon(), IconPosition::Before)
        ->chart(ProfitLoss::getNetSalesArrayForCurrentMonth())
        ->color('warning'),
      Stat::make('Average Income (' . today()->translatedFormat('F Y') . ')', idr(ProfitLoss::getAverageIncomeForCurrentMonth()))
        ->description('Berdasarkan laporan Profit & Loss')
        ->descriptionIcon(ProfitLossResource::getNavigationIcon(), IconPosition::Before)
        ->chart(ProfitLoss::getIncomeArrayForCurrentMonth())
        ->color('success'),
    ];
  }
}
