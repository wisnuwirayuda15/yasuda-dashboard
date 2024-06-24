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

  public function getTabs(): array
  {
    $array = [
      'all' => Tab::make()->icon('fluentui-grid-dots-28-o'),
    ];

    foreach (static::$roles::cases() as $role) {
      $array[$role->value] = Tab::make($role->getLabel())
        // ->icon($role->getIcon())
        ->modifyQueryUsing(fn(Builder $query) => $query->where('role', $role->value));
    }

    return $array;
  }
}
