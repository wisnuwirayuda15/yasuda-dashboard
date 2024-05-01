<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LegrestFleetSeat: int implements HasLabel
{
  case SEAT_SET_1 = 38;

  public function getLabel(): ?string
  {
    return match ($this) {
      self::SEAT_SET_1 => '38 (2-2)',
    };
  }
}