<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\ProfitLossResource;
use App\Filament\Resources\TourReportResource;

class ViewInvoice extends ViewRecord
{
  protected static string $resource = InvoiceResource::class;

  protected function getHeaderActions(): array
  {
    $inv = $this->getRecord();
    $customer = $inv->order->customer;
    $generateInvoiceUrl = route('generate.invoice', $inv->code);
    $profitLossUrl = $inv->profitLoss()->exists() ? ProfitLossResource::getUrl('view', ['record' => $inv->profitLoss->id]) : ProfitLossResource::getUrl('create', ['invoice' => $inv->code]);
    $tourReportUrl = $inv->tourReport()->exists() ? TourReportResource::getUrl('view', ['record' => $inv->tourReport->id]) : TourReportResource::getUrl('create', ['invoice' => $inv->code]);

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
          ->url("https://api.whatsapp.com/send?phone={$customer->phone}&text=$generateInvoiceUrl", true),
        Actions\Action::make('create_or_edit_pnl')
          ->label(($inv->profitLoss ? 'Lihat' : 'Buat') . ' Analisis P&L')
          ->icon(ProfitLossResource::getNavigationIcon())
          ->color('info')
          ->url($profitLossUrl),
        Actions\Action::make('create_or_edit_tour_report')
          ->label(($inv->tourReport ? 'Lihat' : 'Buat') . ' Tour Report')
          ->icon(TourReportResource::getNavigationIcon())
          // ->disabled($tourLeaderNotAllSet)
          // ->tooltip($tourLeaderNotAllSet ? 'Masih ada order yang belum memiliki tour leader' : false)
          ->color('warning')
          ->visible(fn() => $inv->profitLoss || !$tourLeaderNotAllSet)
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
