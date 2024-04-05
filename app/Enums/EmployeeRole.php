<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasDescription;

enum EmployeeRole: string implements HasLabel, HasColor, HasIcon, HasDescription
{
  case OPERASIONAL = 'Operasional';
  case KEUANGAN = 'Keuangan';
  case MARKETING = 'Marketing';
  case MANAGER = 'Manager';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::OPERASIONAL => 'Operasional',
      self::KEUANGAN => 'Keuangan',
      self::MARKETING => 'Marketing',
      self::MANAGER => 'Manager',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::OPERASIONAL => 'info',
      self::KEUANGAN => 'success',
      self::MARKETING => 'warning',
      self::MANAGER => 'primary',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::OPERASIONAL => 'heroicon-s-cog',
      self::KEUANGAN => 'heroicon-s-cash',
      self::MARKETING => 'heroicon-s-megaphone',
      self::MANAGER => 'heroicon-s-user-circle',
    };
  }

  public function getDescription(): ?string
  {
    return match ($this) {
      self::OPERASIONAL => 'Pengurus operasional perusahaan',
      self::KEUANGAN => 'Pengurus keuangan perusahaan',
      self::MARKETING => 'Pengurus promosi dan marketing perusahaan',
      self::MANAGER => 'Pemilik perusahaan',
    };
  }
}
