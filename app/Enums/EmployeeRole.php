<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasDescription;

enum EmployeeRole: string implements HasLabel, HasColor, HasDescription
{
  case Operational = 'operational';
  case Finance = 'finance';
  case Marketing = 'marketing';
  case Manager = 'manager';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::Operational => 'Operasional',
      self::Finance => 'Keuangan',
      self::Marketing => 'Marketing',
      self::Manager => 'Manager',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::Operational => 'info',
      self::Finance => 'warning',
      self::Marketing => 'success',
      self::Manager => 'danger',
    };
  }

  public function getDescription(): ?string
  {
    return match ($this) {
      self::Operational => 'Pengurus operasional perusahaan',
      self::Finance => 'Pengurus keuangan perusahaan',
      self::Marketing => 'Pengurus promosi dan marketing perusahaan',
      self::Manager => 'Pemilik perusahaan',
    };
  }
}