<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\ProfitLossResource;
use Filament\Actions;
use App\Models\Invoice;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\InvoiceResource;
use App\Infolists\Components\InvoiceTemplate;
use App\Models\ProfitLoss;

class ViewInvoice extends ViewRecord
{
  protected static string $resource = InvoiceResource::class;

  protected function getHeaderActions(): array
  {
    $inv = $this->getRecord();
    $customer = $inv->order->customer;
    $url = route('generate.invoice', $inv->code);
    $pnl = $inv->profitLoss ? ProfitLossResource::getUrl('view', ['record' => $inv->profitLoss->id]) : ProfitLossResource::getUrl('create', ['invoice' => $inv->code]);
    return [
      Actions\ActionGroup::make([
        Actions\DeleteAction::make(),
        Actions\EditAction::make(),
        Actions\Action::make('whatsapp_send')
          ->label('Kirim')
          ->tooltip("Kirim detail invoice kepada customer ({$customer->phone}) via Whatsapp")
          ->color('success')
          ->icon('gmdi-whatsapp-r')
          ->url("https://api.whatsapp.com/send?phone={$customer->phone}&text=$url", true),
      ]),
      Actions\Action::make('create_or_edit_pnl')
        ->label(($inv->profitLoss ? 'Lihat' : 'Buat') . ' Analisis P&L')
        ->icon(ProfitLossResource::getNavigationIcon())
        ->color('info')
        // ->hidden(fn() => $inv->profitLoss)
        ->url($pnl),
      Actions\Action::make('export_pdf')
        ->label('Export')
        ->tooltip('Export invoice dalam bentuk PDF')
        ->color('danger')
        ->icon('tabler-pdf')
        ->url($url, true)
    ];
  }
}
