<?php

namespace App\Settings;

use Filament\Support\Enums\MaxWidth;
use Spatie\LaravelSettings\Settings;
use Filament\Tables\Enums\ActionsPosition;

class GeneralSettings extends Settings
{
  public string $site_name;
  public string $site_font;
  public bool $site_spa;
  public bool $site_navigation;
  public bool $table_striped;
  public int $site_logoHeight;
  public ActionsPosition|string $table_actionPosition;
  public MaxWidth|string $site_maxContentWidth;
  public array $color_primary;
  public array $color_secondary;

  public static function group(): string
  {
    return 'general';
  }
}