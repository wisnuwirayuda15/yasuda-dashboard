<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CostDetailCategory: string implements HasLabel, HasColor
{
  case DEFAULT = 'default';
  case MAIN = 'main';
  case FIX = 'fix';
  case VARIABLE = 'var';
  case OTHER = 'other';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::DEFAULT => 'Biaya Default',
      self::MAIN => 'Biaya Utama',
      self::FIX => 'Biaya Tetap',
      self::VARIABLE => 'Biaya Variabel',
      self::OTHER => 'Biaya Lain-lain',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::DEFAULT => 'info',
      self::MAIN => 'success',
      self::FIX => 'danger',
      self::VARIABLE => 'warning',
      self::OTHER => 'primary',
    };
  }
}