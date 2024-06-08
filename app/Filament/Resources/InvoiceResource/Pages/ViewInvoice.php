<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\ProfitLossResource;
use App\Filament\Resources\ShirtResource;
use App\Filament\Resources\TourReportResource;

class ViewInvoice extends ViewRecord
{
  protected static string $resource = InvoiceResource::class;

  protected function getHeaderActions(): array
  {
    $inv = $this->getRecord();

    $customer = $inv->order->customer;

    $generateInvoiceUrl = route('generate.invoice', $inv->code);

    $whatsAppUrl = "https://api.whatsapp.com/send?phone={$customer->phone}&text=$generateInvoiceUrl";

    $shirt = $inv->shirt()->exists();

    $pl = $inv->profitLoss()->exists();

    $tr = $inv->tourReport()->exists();

    $shirtUrl = $shirt ? ShirtResource::getUrl('view', ['record' => $inv->shirt->id]) : ShirtResource::getUrl('create', ['invoice' => $inv->code]);

    $profitLossUrl = $pl ? ProfitLossResource::getUrl('view', ['record' => $inv->profitLoss->id]) : ProfitLossResource::getUrl('create', ['invoice' => $inv->code]);
    
    $tourReportUrl = $tr ? TourReportResource::getUrl('view', ['record' => $inv->tourReport->id]) : TourReportResource::getUrl('create', ['invoice' => $inv->code]);

    $tourLeaderNotAllSet = $inv->order->whereHas('orderFleets', function (Builder $query) {
      $query->whereNull('tour_leader_id');
    })->exists();

    return [
      Actions\ActionGroup::make([
        Actions\EditAction::make(),
        Actions\DeleteAction::make(),
        Actions\Action::make('whatsapp_send')
          ->label('Kirim')
          ->tooltip("Kirim detail invoice kepada customer ({$customer->phone}) via Whatsapp")
          ->color('success')
          ->icon('gmdi-whatsapp-r')
          ->url($whatsAppUrl, true),
        Actions\Action::make('shirt')
          ->label(($shirt ? 'Lihat' : 'Buat') . ' Baju Wisata')
          ->icon(ShirtResource::getNavigationIcon())
          ->color('secondary')
          ->url($shirtUrl),
        Actions\Action::make('pnl')
          ->label(($pl ? 'Lihat' : 'Buat') . ' Analisis P&L')
          ->icon(ProfitLossResource::getNavigationIcon())
          ->color('info')
          ->url($profitLossUrl),
        Actions\Action::make('tour_report')
          ->label(($tr ? 'Lihat' : 'Buat') . ' Tour Report')
          ->icon(TourReportResource::getNavigationIcon())
          ->color('warning')
          ->visible($pl)
          ->hidden($tourLeaderNotAllSet)
          ->url($tourReportUrl),
      ])->tooltip('Menu'),
      Actions\Action::make('export_pdf')
        ->label('Export')
        ->tooltip('Export invoice dalam bentuk PDF')
        ->color('danger')
        ->icon('tabler-pdf')
        ->url($generateInvoiceUrl, true)
    ];
  }
}
