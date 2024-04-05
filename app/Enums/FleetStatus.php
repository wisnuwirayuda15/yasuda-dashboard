<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FleetStatus: string implements HasLabel, HasColor, HasIcon
{
  case AVAILABLE = 'available';
  case ON_TRIP = 'on_trip';
  case CANCELED = 'canceled';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::AVAILABLE => 'Tersedia',
      self::ON_TRIP => 'Dalam Perjalanan',
      self::CANCELED => 'Dibatalakan',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::AVAILABLE => 'heroicon-m-check-circle',
      self::ON_TRIP => 'heroicon-m-information-circle',
      self::CANCELED => 'heroicon-m-x-circle',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::AVAILABLE => 'success',
      self::ON_TRIP => 'primary',
      self::CANCELED => 'danger',
    };
  }
}