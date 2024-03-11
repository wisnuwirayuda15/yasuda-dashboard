<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InstitutionCategory: string implements HasLabel, HasColor
{
  case Umum = 'umum';
  case SD = 'sd';
  case TK = 'tk';
  case SMP = 'smp';
  case SMA = 'sma';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::Umum => 'Umum',
      self::SD => 'SD/MI/MDTA',
      self::TK => 'TK/KB',
      self::SMP => 'SMP/MTS',
      self::SMA => 'SMA/SMK',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::Umum => 'info',
      self::SD => 'warning',
      self::TK => 'success',
      self::SMP => 'primary',
      self::SMA => 'danger',
    };
  }
}