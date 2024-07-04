<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use Filament\Actions;
use App\Enums\EmployeeRole;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmployeeResource;

class ListEmployees extends ListRecords
{
  protected static string $resource = EmployeeResource::class;

  protected static string $roles = EmployeeRole::class;

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
      'tour_leader' => Tab::make('Tour Leader')
        ->icon('gmdi-tour')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('role', EmployeeRole::TOUR_LEADER->value)),
      'other' => Tab::make()
        ->icon(EmployeeResource::getNavigationIcon())
        ->modifyQueryUsing(fn(Builder $query) => $query->whereNot('role', EmployeeRole::TOUR_LEADER->value))
    ];

    // foreach (static::$roles::cases() as $role) {
    //   $array[$role->value] = Tab::make($role->getLabel())
    //     ->modifyQueryUsing(fn(Builder $query) => $query->where('role', $role->value));
    // }

    return $array;
  }
}
