<?php

namespace App\Filament\Resources\TourReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\TourReportResource;

class ListTourReports extends ListRecords
{
  protected static string $resource = TourReportResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('list_invoice')
        ->label('Lihat Invoice')
        ->visible(fn() => auth()->user()->can('view_any_invoice'))
        ->tooltip('Anda bisa membuat tour report dari halaman invoice')
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
