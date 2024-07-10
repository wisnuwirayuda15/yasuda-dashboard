<?php

namespace App\Filament\Widgets;

use App\Models\Fleet;
use App\Models\OrderFleet;
use App\Filament\Resources\FleetResource;
use App\Filament\Resources\OrderFleetResource;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class FleetWidget extends BaseWidget
{
  protected static ?string $pollingInterval = null;

  protected static ?int $sort = -1;

  public function getColumns(): int
  {
    return 2;
  }

  protected function getStats(): array
  {
    return [
      Stat::make('Mitra Armada', Fleet::count())
        ->icon(FleetResource::getNavigationIcon())
        ->description('Mitra armada yang bekerja sama dengan Yasuda Jaya Tour.'),
      Stat::make('Ketersediaan Armada', OrderFleet::count())
        ->icon(OrderFleetResource::getNavigationIcon())
        ->description('Jadwal keberangkatan wisata.'),
    ];
  }
}
