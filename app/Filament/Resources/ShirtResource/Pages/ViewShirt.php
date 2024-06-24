<?php

namespace App\Filament\Resources\ShirtResource\Pages;

use Filament\Actions;
use App\Models\Destination;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\ShirtResource;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ViewShirt extends ViewRecord
{
  protected static string $resource = ShirtResource::class;

  protected function getWhatsAppUrl(string $phone): string
  {
    $shirt = $this->getRecord();

    $inv = $shirt->invoice;

    $order = $inv->order;

    $customer = $order->customer->name;

    $child = $shirt->child;

    $adult = $shirt->adult;

    $male = $shirt->male_teacher;

    $female = $shirt->female_teacher;

    $total = $shirt->total;

    $des = Destination::find($order->destinations);

    $destinations = "{$order->regency->name} ({$des->implode('name', ' + ')})";

    $text = "*FORMAT PEMESANAN KAOS WISATA YASUDA JAYA TOUR*\n\n";

    $text .= "*Nama Sekolah:* {$customer}\n";
    $text .= "*Tanggal Pelaksanaan:* {$order->trip_date->translatedFormat('d F Y')}\n";
    $text .= "*Tujuan Wisata:* {$destinations}\n\n";

    if (filled($child)) {
      $text .= "*SISWA*\n";
      $text .= "Warna: {$shirt->child_color}\n";
      $text .= "Bahan (PE/Katun): {$shirt->child_material}\n";
      $text .= "Ukuran:\n";
      foreach ($child as $item) {
        $text .= strtoupper($item['size']) . ": " . $item['qty'] . "\n";
      }
      $text .= "\n\n";
    }

    if (filled($male) || filled($female)) {
      $text .= "*GURU*\n";

      if (filled($male)) {
        $text .= "Guru Laki-Laki:\n";
        $text .= "Warna: {$shirt->male_teacher_color}\n";
        $text .= "Bahan (PE/Katun): {$shirt->male_teacher_material}\n";
        $text .= "Ukuran:\n";
        foreach ($male as $item) {
          $text .= strtoupper($item['size']) . ": " . $item['qty'] . "\n";
        }
        $text .= "\n";
      }

      if (filled($female)) {
        $text .= "Guru Perempuan:\n";
        $text .= "Warna: {$shirt->female_teacher_color}\n";
        $text .= "Bahan (PE/Katun): {$shirt->female_teacher_material}\n";
        $text .= "Ukuran:\n";
        foreach ($female as $item) {
          $text .= strtoupper($item['size']) . ": " . $item['qty'] . "\n";
        }
        $text .= "\n\n";
      }
    }

    if (filled($adult)) {
      $text .= "*WALI SISWA*\n";
      $text .= "Warna: {$shirt->adult_color}\n";
      $text .= "Ukuran:\n";
      foreach ($adult as $item) {
        $text .= strtoupper($item['size']) . ": " . $item['qty'] . "\n";
      }
      $text .= "\n\n";
    }

    $text .= "Total: {$total} Kaos\n\n";

    $text .= "*Note*\n";
    $text .= "- _Untuk guru mendapatkan maksimal 2 stell/bus kecil, dan 4 stell/bus besar_\n";
    $text .= "- _Mohon dituliskan secara detail destinasi wisatanya/bukan hanya kota saja_\n";

    $text = urlencode($text);
    
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
        ->action(function (array $data) {
          $this->getRecord()->update(['status' => 'sent']);
          redirect(static::getWhatsAppUrl($data['vendor_phone']));
        }),
      Actions\EditAction::make(),
    ];
  }
}
