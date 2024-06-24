<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CashFlow: string implements HasLabel, HasColor, HasIcon
{
  case IN = 'inflow';
  case OUT = 'outflow';

  public function getLabel(): ?string
  {
    return ucwords($this->value);
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::IN => 'success',
      self::OUT => 'danger',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::IN => 'uni-money-withdraw-o',
      self::OUT => 'uni-money-insert-o',
    };
  }
}

