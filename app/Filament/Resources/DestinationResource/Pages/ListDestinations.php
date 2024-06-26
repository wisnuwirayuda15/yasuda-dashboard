<?php

namespace App\Filament\Resources\DestinationResource\Pages;

use App\Enums\DestinationType;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DestinationResource;

class ListDestinations extends ListRecords
{
  protected static string $resource = DestinationResource::class;

  protected static string $types = DestinationType::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  public function getTabs(): array
  {
    $array = [
      'all' => Tab::make()->icon('fluentui-grid-dots-28-o'),
    ];

    foreach (static::$types::cases() as $type) {
      $array[$type->value] = Tab::make($type->getLabel())
        ->icon($type->getIcon())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('type', $type->value));
    }

    return $array;
  }
}
