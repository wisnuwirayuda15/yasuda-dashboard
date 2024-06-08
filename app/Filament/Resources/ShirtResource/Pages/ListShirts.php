<?php

namespace App\Filament\Resources\ShirtResource\Pages;

use Filament\Actions;
use App\Filament\Resources\ShirtResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\InvoiceResource;

class ListShirts extends ListRecords
{
  protected static string $resource = ShirtResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('list_invoice')
        ->label('Lihat Invoice')
        ->tooltip('Anda bisa membuat list baju dari halaman invoice')
        ->icon(InvoiceResource::getNavigationIcon())
        ->url(InvoiceResource::getUrl('index')),
    ];
  }
}
