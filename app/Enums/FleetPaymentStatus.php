<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;

enum FleetPaymentStatus: string implements HasLabel, HasColor, HasIcon
{
  case NON_DP = 'non_dp';
  case DP = 'dp';
  case TRANSFER = 'tf';
  case CASH = 'cash';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::NON_DP => 'Booked - Non DP',
      self::DP => 'Booked - DP',
      self::TRANSFER => 'Transfer',
      self::CASH => 'Cash',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::NON_DP => 'fluentui-money-off-20',
      self::DP => 'bxs-dollar-circle',
      self::TRANSFER => 'heroicon-s-credit-card',
      self::CASH => 'fluentui-money-hand-20',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::DP => 'info',
      self::NON_DP => 'danger',
      self::TRANSFER => 'success',
      self::CASH => 'warning',
    };
  }
}