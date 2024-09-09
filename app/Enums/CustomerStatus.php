<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CustomerStatus: string implements HasLabel, HasColor, HasIcon
{
  case CANDIDATE = 'candidate';
  case NEW = 'new';
  case SUBSCRIBER = 'subscriber';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::CANDIDATE => 'Calon Customer',
      self::NEW => 'Potensial',
      self::SUBSCRIBER => 'Berlangganan',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::CANDIDATE => 'warning',
      self::NEW => 'info',
      self::SUBSCRIBER => 'success',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::CANDIDATE => 'fas-question',
      self::NEW => 'heroicon-s-sparkles',
      self::SUBSCRIBER => 'heroicon-s-check-badge',
    };
  }
}