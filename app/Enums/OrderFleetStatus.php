<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderFleetStatus: string implements HasLabel, HasColor
{
  case READY = 'ready';

  case ON_TRIP = 'on_trip';

  case FINISHED = 'finished';

  case BOOKED = 'booked';

  case CANCELED = 'canceled';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::READY => 'Belum Berangkat',
      self::ON_TRIP => 'Dalam Perjalanan',
      self::FINISHED => 'Selesai',
      self::BOOKED => 'Dipesan',
      self::CANCELED => 'Dibatalkan',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::READY => Color::Indigo,
      self::ON_TRIP => Color::Sky,
      self::FINISHED => Color::Green,
      self::BOOKED => Color::Yellow,
      self::CANCELED => Color::Red,
    };
  }
}
