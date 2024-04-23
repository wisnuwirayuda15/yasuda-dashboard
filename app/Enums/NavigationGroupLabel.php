<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroupLabel: string implements HasLabel
{
  case MASTER_DATA = 'Master Data';
  case OPERATIONAL = 'Operational';

  public function getLabel(): ?string
  {
    return $this->name;
  }
}