<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroupLabel: string implements HasLabel
{
  case OPERATIONAL = 'Operational';
  case FINANCE = 'Finance';
  case MARKETING = 'Marketing';
  case MASTER_DATA = 'Master Data';
  case SETTING = 'Settings';
  
  public function getLabel(): ?string
  {
    return $this->name;
  }
}