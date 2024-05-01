<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BigFleetSeat: int implements HasLabel
{
  case SEAT_SET_1 = 50;
  case SEAT_SET_2 = 52;
  case SEAT_SET_3 = 60;
  case SEAT_SET_4 = 61;

  public function getLabel(): ?string
  {
    return match ($this) {
      self::SEAT_SET_1 => '50 (2-2)',
      self::SEAT_SET_2 => '52 (2-2)',
      self::SEAT_SET_3 => '60 (2-3)',
      self::SEAT_SET_4 => '61 (2-3)',
    };
  }
}
