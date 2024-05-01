<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MediumFleetSeat: int implements HasLabel
{
  case SEAT_SET_1 = 31;
  case SEAT_SET_2 = 33;
  case SEAT_SET_3 = 35;

  public function getLabel(): ?string
  {
    return match ($this) {
      self::SEAT_SET_1 => '31 (2-2)',
      self::SEAT_SET_2 => '33 (2-2)',
      self::SEAT_SET_3 => '35 (2-2)',
    };
  }
}