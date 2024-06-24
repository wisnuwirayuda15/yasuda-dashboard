<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmployeeRole: string implements HasLabel
{
  case OPERATIONAL_STAFF = 'operational_staff';
  case OPERATIONAL_MANAGER = 'operational_manager';
  case FINANCE_STAFF = 'finance_staff';
  case FINANCE_MANAGER = 'finance_manager';
  case MARKETING_STAFF = 'marketing_staff';
  case MARKETING_MANAGER = 'marketing_manager';
  case MANAGER = 'super_admin';
  // case TOUR_LEADER = 'tour_leader';

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
      // self::TOUR_LEADER => 'Tour Leader',
    };
  }
}
