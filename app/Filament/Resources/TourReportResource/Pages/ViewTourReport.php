<?php

namespace App\Filament\Resources\TourReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\TourReportResource;

class ViewTourReport extends ViewRecord
{
  protected static string $resource = TourReportResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('view_invoice')
        ->label('Lihat Invoice')
        ->icon(InvoiceResource::getNavigationIcon())
        ->color('info')
        ->url(InvoiceResource::getUrl('view', ['record' => $this->getRecord()->invoice->id])),
      Actions\EditAction::make(),
    ];
  }
}
