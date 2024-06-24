<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmployeeStatus: string implements HasLabel, HasColor, HasIcon
{
  case PERMANENT = 'permanent';
  case FREELANCE = 'freelance';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::PERMANENT => 'Pegawai Tetap',
      self::FREELANCE => 'Freelance'
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::PERMANENT => 'gmdi-verified-user-r',
      self::FREELANCE => 'gmdi-work-history-r',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::PERMANENT => 'success',
      self::FREELANCE => 'warning',
    };
  }
}