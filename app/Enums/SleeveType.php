<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SleeveType: string implements HasLabel, HasColor
{
  case SHORT = 'short';
  case REGLAN = 'reglan';
  case LONG = 'long';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::SHORT => 'Pendek',
      self::REGLAN => 'Reglan',
      self::LONG => 'Panjang',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::SHORT => 'success',
      self::REGLAN => 'warning',
      self::LONG => 'danger',
    };
  }
}

