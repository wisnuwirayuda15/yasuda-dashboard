<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum FleetCategory: string implements HasLabel, HasColor, HasIcon
{
  case MEDIUM = 'medium';
  case BIG = 'big';
  case LEGREST = 'legrest';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::MEDIUM => 'Medium',
      self::BIG => 'Big',
      self::LEGREST => 'Legrest',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::MEDIUM => 'fas-bus-simple',
      self::BIG => 'fas-bus-alt',
      self::LEGREST => 'bxs-bus-school',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::MEDIUM => 'success',
      self::BIG => 'info',
      self::LEGREST => 'danger',
    };
  }
}