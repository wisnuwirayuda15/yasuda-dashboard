<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroupLabel: string implements HasLabel
{
  case OPERATIONAL = 'operational';

  case FINANCE = 'finance';

  case MARKETING = 'marketing';

  case MASTER_DATA = 'master_data';

  case SETTING = 'settings';

  case HR = 'human_resource';

  case OTHER = 'other';

  public function getLabel(): ?string
  {
    return __("navigation.{$this->value}");
  }
}
