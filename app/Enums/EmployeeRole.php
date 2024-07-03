<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmployeeRole: string implements HasLabel, HasColor
{
  case OPERATIONAL_STAFF = 'operational_staff';

  case OPERATIONAL_MANAGER = 'operational_manager';

  case FINANCE_STAFF = 'finance_staff';

  case FINANCE_MANAGER = 'finance_manager';

  case MARKETING_STAFF = 'marketing_staff';

  case MARKETING_MANAGER = 'marketing_manager';

  case MANAGER = 'super_admin';

  case TOUR_LEADER = 'tour_leader';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::OPERATIONAL_STAFF => 'Operational & Logistic Staff',
      self::OPERATIONAL_MANAGER => 'Operational & Logistic Manager',
      self::FINANCE_STAFF => 'Finance Staff',
      self::FINANCE_MANAGER => 'Finance Manager',
      self::MARKETING_STAFF => 'Sales & Marketing Staff',
      self::MARKETING_MANAGER => 'Sales & Marketing Manager',
      self::MANAGER => 'Company Manager',
      self::TOUR_LEADER => 'Tour Leader',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::OPERATIONAL_STAFF => Color::Sky,
      self::OPERATIONAL_MANAGER => Color::Lime,
      self::FINANCE_STAFF => Color::Teal,
      self::FINANCE_MANAGER => Color::Emerald,
      self::MARKETING_STAFF => Color::Pink,
      self::MARKETING_MANAGER => Color::Orange,
      self::MANAGER => Color::Green,
      self::TOUR_LEADER => Color::Violet,
    };
  }
}

