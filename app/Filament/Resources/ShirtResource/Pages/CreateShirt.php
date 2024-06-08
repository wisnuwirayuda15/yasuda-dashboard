<?php

namespace App\Filament\Resources\ShirtResource\Pages;

use Filament\Actions;
use App\Models\Invoice;
use App\Filament\Resources\ShirtResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShirt extends CreateRecord
{
  protected static string $resource = ShirtResource::class;

  protected static bool $canCreateAnother = false;

  public function beforeFill(): void
  {
    $shirt = Invoice::where('code', request('invoice'))->firstOrFail()->shirt;

    // Each invoice should only has one profit & loss
    if ($shirt) {
      redirect(ShirtResource::getUrl('view', ['record' => $shirt->id]));
    }
  }
}
