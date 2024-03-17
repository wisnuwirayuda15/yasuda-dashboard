<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderPaymentStatus: string implements HasLabel, HasColor
{
  case DP = 'dp';
  case NON_DP = 'ndp';
  case TRANSFER = 'tf';
  case CASH = 'cash';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::DP => 'Booked - DP',
      self::NON_DP => 'Booked - Non DP',
      self::TRANSFER => 'Booked - Transfer',
      self::CASH => 'Booked - Cash',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::DP => 'info',
      self::NON_DP => 'yellow',
      self::TRANSFER => 'success',
      self::CASH => 'success',
    };
  }
}