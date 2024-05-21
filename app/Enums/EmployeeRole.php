<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasDescription;

enum EmployeeRole: string implements HasLabel, HasColor, HasIcon
{
  case OPERATIONAL = 'operational';
  case FINANCE = 'finance';
  case MARKETING = 'marketing';
  case MANAGER = 'super_admin';
  case TOUR_LEADER = 'tour_leader';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::OPERATIONAL => 'Operational',
      self::FINANCE => 'Finance',
      self::MARKETING => 'Marketing',
      self::MANAGER => 'Manager',
      self::TOUR_LEADER => 'Tour Leader',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::OPERATIONAL => 'info',
      self::FINANCE => 'success',
      self::MARKETING => 'warning',
      self::MANAGER => 'primary',
      self::TOUR_LEADER => 'danger',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::OPERATIONAL => 'heroicon-s-cog',
      self::FINANCE => 'heroicon-s-cash',
      self::MARKETING => 'heroicon-s-megaphone',
      self::MANAGER => 'heroicon-s-user-circle',
      self::TOUR_LEADER => 'heroicon-s-user-group',
    };
  }
}
