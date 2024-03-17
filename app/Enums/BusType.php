<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BusType: string implements HasLabel, HasColor
{
  case BIG = 'big';
  case MEDIUM = 'medium';
  case LEGREST = 'legrest';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::BIG => 'Big Bus',
      self::MEDIUM => 'Medium Bus',
      self::LEGREST => 'Legrest',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::BIG => 'info',
      self::MEDIUM => 'success',
      self::LEGREST => 'danger',
    };
  }
}