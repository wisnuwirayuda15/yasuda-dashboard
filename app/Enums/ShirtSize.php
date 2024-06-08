<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ShirtSize: string implements HasLabel, HasColor, HasIcon
{
  case XS = 'xs';
  case S = 's';
  case M = 'm';
  case L = 'l';
  case XL = 'xl';
  case XXL = 'xxl';
  case XXXL = '3xl';

  public function getLabel(): ?string
  {
    return strtoupper($this->name);
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::XS => 'mdi-size-xs',
      self::S => 'mdi-size-s',
      self::M => 'mdi-size-m',
      self::L => 'mdi-size-l',
      self::XL => 'mdi-size-xl',
      self::XXL => 'mdi-size-xxl',
      self::XXXL => 'mdi-size-xxxl',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::XS => Color::Sky,
      self::S => Color::Blue,
      self::M => Color::Emerald,
      self::L => Color::Green,
      self::XL => Color::Yellow,
      self::XXL => Color::Red,
    };
  }
}
