<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InvoiceStatus: string implements HasLabel, HasColor, HasIcon
{
  case PAID_OFF = 'paid_off';
  case UNDER_PAYMENT = 'under_payment';
  case OVER_PAYMENT = 'over_payment';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::PAID_OFF => 'Lunas',
      self::UNDER_PAYMENT => 'Kurang Bayar',
      self::OVER_PAYMENT => 'Lebih Bayar',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::PAID_OFF => 'heroicon-s-check-badge',
      self::UNDER_PAYMENT => 'heroicon-s-x-circle',
      self::OVER_PAYMENT => 'heroicon-s-information-circle',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::PAID_OFF => Color::Emerald,
      self::UNDER_PAYMENT => Color::Red,
      self::OVER_PAYMENT => Color::Yellow,
    };
  }
}