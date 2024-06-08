<?php

namespace App\Filament\Resources\ShirtResource\Pages;

use App\Filament\Resources\ShirtResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewShirt extends ViewRecord
{
  protected static string $resource = ShirtResource::class;

  protected function getWhatsAppUrl(): string
  {
    $shirt = $this->getRecord();

    $phone = '6281283435423';

    $customer = $shirt->invoice->order->customer->name;

    $child = $shirt->child;

    $adult = $shirt->adult;

    $total = $shirt->total;

    $message = "{$customer}\n\n";

    if (filled($child)) {
      $message .= "Ukuran Baju Anak\n";
      foreach ($child as $item) {
        $message .= strtoupper($item['size']) . ": " . $item['qty'] . "\n";
      }
    }

    if (filled($adult)) {
      $message .= "\nUkuran Dewasa\n";
      foreach ($adult as $item) {
        $message .= strtoupper($item['size']) . ": " . $item['qty'] . "\n";
      }
    }

    $message .= "\nTotal: " . $total;

    $text = urlencode($message);

    $whatsAppUrl = "https://wa.me/{$phone}?text={$text}";

    return $whatsAppUrl;
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('whatsapp_send')
        ->label('Kirim')
        ->tooltip('Kirim WhatsApp')
        ->color('success')
        ->icon('gmdi-whatsapp-r')
        ->url(static::getWhatsAppUrl(), true),
      Actions\EditAction::make(),
    ];
  }
}
