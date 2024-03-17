<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BusStatus: string implements HasLabel, HasColor, HasIcon
{
  case Available = 'available';
  case OnTrip = 'on_trip';
  case Canceled = 'canceled';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::Available => 'Tersedia',
      self::OnTrip => 'Dalam Perjalanan',
      self::Canceled => 'Dibatalakan',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::Available => 'heroicon-m-check-circle',
      self::OnTrip => 'heroicon-m-information-circle',
      self::Canceled => 'heroicon-m-x-circle',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::Available => 'success',
      self::OnTrip => 'primary',
      self::Canceled => 'danger',
    };
  }
}