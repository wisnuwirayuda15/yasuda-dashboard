<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShirtMaterial: string implements HasLabel, HasColor
{
  case PE = 'PE';
  case COTTON = 'Katun';

  public function getLabel(): ?string
  {
    return $this->value;
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::PE => 'success',
      self::COTTON => 'danger',
    };
  }
}

