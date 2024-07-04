<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\InvoiceResource;

class ListInvoices extends ListRecords
{
  protected static string $resource = InvoiceResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->disabled(fn() => Order::doesntHave('invoice')->has('orderFleets')->count() === 0),
    ];
  }

  public function setPage($page, $pageName = 'page'): void
  {
    parent::setPage($page, $pageName);
    $this->dispatch(\App\Enums\JavascriptEvent::SCROLL_TO_TOP->value);
  }
}
