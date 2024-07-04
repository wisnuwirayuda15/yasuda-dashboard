<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use App\Models\Customer;
use App\Enums\CustomerStatus;
use App\Enums\CustomerCategory;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CustomerResource;

class ListCustomers extends ListRecords
{
  protected static string $resource = CustomerResource::class;

  protected static string $categories = CustomerCategory::class;

  protected static string $status = CustomerStatus::class;

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

    // foreach (static::$categories::cases() as $category) {
    //   if (Customer::query()->where('category', $category->value)->exists()) {
    //     $array[$category->value] = Tab::make($category->getLabel())
    //       ->icon($category->getIcon())
    //       ->modifyQueryUsing(fn(Builder $query) => $query->where('category', $category->value));
    //   }
    // }

    foreach (static::$status::cases() as $status) {
      if (Customer::query()->where('status', $status->value)->exists()) {
        $array[$status->value] = Tab::make($status->getLabel())
          ->icon($status->getIcon())
          ->modifyQueryUsing(fn(Builder $query) => $query->where('status', $status->value));
      }
    }

    return $array;
  }
}
