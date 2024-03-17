<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BannerStatus: string implements HasLabel, HasColor
{
  case PRINTED = 'dp';
  case NOT_YET = 'ndp';
  case NONE = 'tf';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::PRINTED => 'Tercetak',
      self::NOT_YET => 'Belum Dibikini',
      self::NONE => 'Tidak Ada',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::PRINTED => 'success',
      self::NOT_YET => 'yellow',
      self::NONE => 'danger',
    };
  }
}