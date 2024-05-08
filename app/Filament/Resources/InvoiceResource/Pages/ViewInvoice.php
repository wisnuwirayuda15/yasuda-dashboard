<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Models\Invoice;
use Spatie\LaravelPdf\Facades\Pdf;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\InvoiceResource;
use App\Infolists\Components\InvoiceTemplate;

class ViewInvoice extends ViewRecord
{
  protected static string $resource = InvoiceResource::class;

  protected function getHeaderActions(): array
  {
    $inv = $this->getRecord();
    $customer = $inv->order->customer;
    return [
      Actions\EditAction::make(),
      Actions\Action::make('create_pnl')
        ->label('Create Profit & Loss'),
      Actions\Action::make('whatsapp_send')
        ->label('Send')
        ->tooltip('Kirim detail invoice kepada customer via Whatsapp')
        ->color('success')
        ->icon('gmdi-whatsapp-r')
        ->url("https://api.whatsapp.com/send?phone={$customer->phone}&text=test", true),
      Actions\Action::make('export_pdf')
        ->label('Export')
        ->tooltip('Export invoice dalam bentuk PDF')
        ->color('danger')
        ->icon('fas-file-pdf')
        ->url(route('generate.invoice', $inv->id), true)
    ];
  }
}
