<?php

namespace App\Filament\Resources\FleetResource\Pages;

use App\Models\Fleet;
use Filament\Actions;
use App\Enums\FleetCategory;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\FleetResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFleets extends ListRecords
{
  protected static string $resource = FleetResource::class;

  protected static string $categories = FleetCategory::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  public function setPage($page, $pageName = 'page'): void
  {
    parent::setPage($page, $pageName);
    $this->dispatch(\App\Enums\JavascriptEvent::SCROLL_TO_TOP->value);
  }

  public function getTabs(): array
  {
    $array = [
      'all' => Tab::make()->icon('fluentui-grid-dots-28-o'),
    ];

    foreach (static::$categories::cases() as $category) {
      if (Fleet::withoutGlobalScopes()->where('category', $category->value)->exists()) {
        $array[$category->value] = Tab::make($category->getLabel())
          ->icon($category->getIcon())
          ->modifyQueryUsing(fn(Builder $query) => $query->where('category', $category->value));
      }
    }

    return $array;
  }
}
