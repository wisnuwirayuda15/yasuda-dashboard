<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CustomerCategory: string implements HasLabel, HasColor, HasIcon
{
  case TK = 'tk';
  case SD = 'sd';
  case SMP = 'smp';
  case SMA = 'sma';
  case UMUM = 'umum';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::UMUM => 'Umum',
      self::SD => 'SD Sederajat',
      self::TK => 'TK Sederajat',
      self::SMP => 'SMP Sederajat',
      self::SMA => 'SMA Sederajat',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::UMUM => 'heroicon-s-user-group',
      self::SD => 'fas-child',
      self::TK => 'maki-playground',
      self::SMP => 'fas-book-open',
      self::SMA => 'fas-book',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::UMUM => 'info',
      self::SD => 'warning',
      self::TK => 'success',
      self::SMP => 'primary',
      self::SMA => 'secondary',
    };
  }
}