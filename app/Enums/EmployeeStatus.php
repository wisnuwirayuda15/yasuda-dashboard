<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmployeeStatus: string implements HasLabel, HasColor, HasIcon
{
  case PERMANENT = 'permanent';

  case FREELANCE = 'freelance';

  case RESIGN = 'resign';

  case RETIRE = 'retire';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::PERMANENT => 'Pegawai Tetap',
      self::FREELANCE => 'Freelance',
      self::RESIGN => 'Mengundurkan Diri',
      self::RETIRE => 'Pensiun',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::PERMANENT => 'gmdi-verified-user-r',
      self::FREELANCE => 'gmdi-work-history-r',
      self::RESIGN => 'gmdi-work-off-r',
      self::RETIRE => 'tabler-old',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::PERMANENT => 'success',
      self::FREELANCE => 'warning',
      self::RESIGN => 'danger',
      self::RETIRE => 'secondary',
    };
  }
}
