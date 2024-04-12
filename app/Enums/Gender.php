<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel, HasColor, HasIcon
{
  case MALE = 'male';
  case FEMALE = 'female';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::MALE => 'Laki-laki',
      self::FEMALE => 'Perempuan',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::MALE => 'fas-male',
      self::FEMALE => 'fas-female',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::MALE => Color::Sky,
      self::FEMALE => Color::Rose,
    };
  }
}