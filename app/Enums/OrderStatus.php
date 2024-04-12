<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel, HasColor
{
  case READY = 'ready';
  case ON_TRIP = 'on_trip';
  case FINISHED = 'finished';
  case CANCELED = 'canceled';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::READY => 'Belum Berangkat',
      self::ON_TRIP => 'Dalam Perjalanan',
      self::FINISHED => 'Selesai',
      self::CANCELED => 'Dibatalkan',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::READY => 'info',
      self::ON_TRIP => 'warning',
      self::FINISHED => 'success',
      self::CANCELED => 'danger',
    };
  }
}