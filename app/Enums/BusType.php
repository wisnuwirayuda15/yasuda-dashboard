<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasDescription;

enum BusType: string implements HasLabel, HasColor, HasDescription, HasIcon
{
  case Big = 'big';
  case Medium = 'medium';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::Big => 'Big Bus',
      self::Medium => 'Medium Bus',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::Big => 'heroicon-m-arrow-up',
      self::Medium => 'heroicon-m-arrow-down',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::Big => 'info',
      self::Medium => 'success',
    };
  }

  public function getDescription(): ?string
  {
    return match ($this) {
      self::Big => '50 kursi',
      self::Medium => '25 kursi',
    };
  }
}