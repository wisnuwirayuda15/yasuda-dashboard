<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LegrestFleetSeat: string implements HasLabel
{
  case SEAT_SET_1 = '36';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::SEAT_SET_1 => '36 (2-2)',
    };
  }
}