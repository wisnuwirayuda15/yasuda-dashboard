<?php

namespace App\Filament\Resources\OrderFleetResource\Pages;

use App\Filament\Resources\OrderFleetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderFleets extends ListRecords
{
  protected static string $resource = OrderFleetResource::class;

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
}
