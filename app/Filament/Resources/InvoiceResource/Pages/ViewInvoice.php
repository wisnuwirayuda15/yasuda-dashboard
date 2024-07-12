<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\ShirtResource;
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

    $user = auth()->user();

    $approved = $inv->isApprovalCompleted();

    $customer = $inv->order->customer;

    $generateInvoiceUrl = route('generate.invoice', $inv->code);

    $whatsAppUrl = "https://api.whatsapp.com/send?phone={$customer->phone}&text=$generateInvoiceUrl";

    $shirt = $inv->shirt()->exists();

    $pnl = $inv->profitLoss()->exists();

    $tr = $inv->tourReport()->exists();

    $canCreateShirt = $user->can('create_shirt');

    $canCreateInvoice = $user->can('create_invoice');

    $canCreateProfitLoss = $user->can('create_profit::loss');

    $canCreateTourReport = $user->can('create_tour::report');

    $shirtUrl = $shirt ? ShirtResource::getUrl('view', ['record' => $inv->shirt->id]) : ShirtResource::getUrl('create', ['invoice' => $inv->code]);

    $profitLossUrl = $pnl ? ProfitLossResource::getUrl('view', ['record' => $inv->profitLoss->id]) : ProfitLossResource::getUrl('create', ['invoice' => $inv->code]);

    $tourReportUrl = $tr ? TourReportResource::getUrl('view', ['record' => $inv->tourReport->id]) : TourReportResource::getUrl('create', ['invoice' => $inv->code]);

    $tourLeaderNotAllSet = $inv->order()->whereHas('orderFleets', function (Builder $query) {
      $query->whereNull('employee_id');
    })->exists();

    return [
      ActionGroup::make([
        DeleteAction::make(),
        Action::make('whatsapp_send')
          ->label('Kirim')
          ->tooltip("Kirim detail invoice kepada customer ({$customer->phone}) via Whatsapp")
          ->color('success')
          ->icon('gmdi-whatsapp-r')
          ->visible($canCreateInvoice)
          ->url($whatsAppUrl, true),
        Action::make('shirt')
          ->label(($shirt ? 'Lihat' : 'Buat') . ' Baju Wisata')
          ->icon(ShirtResource::getNavigationIcon())
          ->color('secondary')
          ->visible($canCreateShirt)
          ->url($shirtUrl),
        Action::make('pnl')
          ->label(($pnl ? 'Lihat' : 'Buat') . ' Analisis P&L')
          ->icon(ProfitLossResource::getNavigationIcon())
          ->color('info')
          ->visible($canCreateProfitLoss)
          ->url($profitLossUrl),
        Action::make('tour_report')
          ->label(($tr ? 'Lihat' : 'Buat') . ' Tour Report')
          ->icon(TourReportResource::getNavigationIcon())
          ->color('warning')
          ->visible($pnl && $canCreateTourReport)
          ->hidden($tourLeaderNotAllSet)
          ->url($tourReportUrl),
      ])->tooltip('Menu')
        ->visible($approved),
      Action::make('export_pdf')
        ->label('Export')
        ->tooltip('Export invoice dalam bentuk PDF')
        ->color('danger')
        ->icon('tabler-pdf')
        ->url($generateInvoiceUrl, true)
        ->visible($approved),
      EditAction::make()->visible($approved),
      Action::make('not_approved')
        ->disabled()
        ->label('Not Aprroved')
        ->color('danger')
        ->icon('heroicon-s-x-circle')
        ->hidden($approved),
    ];
  }
}
