<?php

namespace App\Filament\Resources\ShirtResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\ShirtResource;
use Illuminate\Support\HtmlString;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ViewShirt extends ViewRecord
{
  protected static string $resource = ShirtResource::class;

  protected function getWhatsAppUrl(string $phone): string
  {
    $shirt = $this->getRecord();

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
        ->form([
          PhoneInput::make('vendor_phone')
            ->required()
            ->label('Nomor Vendor')
            ->default(env('SHIRT_VENDOR_PHONE'))
            ->idDefaultFormat(),
        ])
        ->label('Kirim')
        ->tooltip('Kirim informasi baju kepada vendor via WhatsApp')
        ->color('success')
        ->icon('gmdi-whatsapp-r')
        ->action(fn(array $data) => redirect(static::getWhatsAppUrl($data['vendor_phone']))),
      Actions\EditAction::make(),
    ];
  }
}
