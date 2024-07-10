<?php

namespace App\Filament\Resources\ProfitLossResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\ProfitLossResource;

class ListProfitLosses extends ListRecords
{
  protected static string $resource = ProfitLossResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('list_invoice')
        ->label('Lihat Invoice')
        ->visible(fn() => auth()->user()->can('view_any_invoice'))
        ->tooltip('Anda bisa membuat analisis profit & loss dari halaman invoice')
        ->icon(InvoiceResource::getNavigationIcon())
        ->url(InvoiceResource::getUrl('index')),
    ];
  }

  public function setPage($page, $pageName = 'page'): void
  {
    parent::setPage($page, $pageName);
    $this->dispatch(\App\Enums\JavascriptEvent::SCROLL_TO_TOP->value);
  }
}
