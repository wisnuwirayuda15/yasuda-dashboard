<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use App\Enums\CustomerCategory;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CustomerResource;

class ListCustomers extends ListRecords
{
  protected static string $resource = CustomerResource::class;

  protected static string $categories = CustomerCategory::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }

  public function setPage($page, $pageName = 'page'): void
  {
    parent::setPage($page, $pageName);
    $this->dispatch('scroll-to-top');
  }

  public function getTabs(): array
  {
    $array = [
      'all' => Tab::make()->icon('fluentui-grid-dots-28-o'),
    ];

    foreach (static::$categories::cases() as $category) {
      $array[$category->value] = Tab::make($category->getLabel())
        ->icon($category->getIcon())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('category', $category->value));
    }

    return $array;
  }
}
