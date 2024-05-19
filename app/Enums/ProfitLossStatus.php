<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProfitLossStatus: string implements HasLabel, HasColor, HasIcon
{
  case GOOD = 'good';
  case BAD = 'bad';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::GOOD => 'Good Profit',
      self::BAD => 'Tidak Layak',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::GOOD => 'gmdi-thumb-up-alt',
      self::BAD => 'gmdi-thumb-down-alt',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::GOOD => Color::Green,
      self::BAD => Color::Red,
    };
  }
}