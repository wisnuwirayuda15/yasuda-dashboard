<?php

namespace App\Filament\Widgets;

use App\Models\ProfitLoss;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\IconPosition;
use App\Filament\Resources\ProfitLossResource;
use App\Filament\Resources\TourReportResource;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ProfitLossWidget extends BaseWidget
{
  use HasWidgetShield;

  protected static ?string $pollingInterval = null;

  protected static ?int $sort = -2;

  public function getColumns(): int
  {
    return 2;
  }

  protected function getStats(): array
  {
    $month = today()->translatedFormat('F Y');

    $icon = ProfitLossResource::getNavigationIcon();

    $iconPosition = IconPosition::Before;

    return [
      Stat::make("Total Penjualan ($month)", idr(ProfitLoss::getTotalNetSalesForCurrentMonth()))
        ->color('warning')
        ->chart(ProfitLoss::getNetSalesArrayForCurrentMonth())
        ->url(ProfitLossResource::getUrl())
        ->descriptionIcon($icon, $iconPosition)
        ->description(new HtmlString('Berdasarkan laporan <strong>Profit & Loss</strong>')),

      Stat::make("Total Pendapatan ($month)", idr(ProfitLoss::getTotalIncomeForCurrentMonth()))
        ->color('success')
        ->chart(ProfitLoss::getIncomeArrayForCurrentMonth())
        ->url(TourReportResource::getUrl())
        ->descriptionIcon($icon, $iconPosition)
        ->description(new HtmlString('Berdasarkan laporan <strong>Profit & Loss</strong> dan <strong>Tour Report</strong>')),
    ];
  }
}
