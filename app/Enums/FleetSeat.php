<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FleetSeat: string implements HasLabel
{
  case SEAT_SET_1 = '31';
  case SEAT_SET_2 = '33';
  case SEAT_SET_3 = '35';
  case SEAT_SET_4 = '50';
  case SEAT_SET_5 = '52';
  case SEAT_SET_6 = '60';
  case SEAT_SET_7 = '61';
  case SEAT_SET_8 = '36';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::SEAT_SET_1 => '31 (2-2)',
      self::SEAT_SET_2 => '33 (2-2)',
      self::SEAT_SET_3 => '35 (2-2)',
      self::SEAT_SET_4 => '50 (2-2)',
      self::SEAT_SET_5 => '52 (2-2)',
      self::SEAT_SET_6 => '60 (2-3)',
      self::SEAT_SET_7 => '61 (2-3)',
      self::SEAT_SET_8 => '36 (2-2)',
    };
  }
}