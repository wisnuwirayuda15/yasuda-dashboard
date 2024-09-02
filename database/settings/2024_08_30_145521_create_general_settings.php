<?php

use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Enums\ActionsPosition;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
  public function up(): void
  {
    $this->migrator->inGroup('general', function (SettingsBlueprint $blueprint): void {
      $blueprint->add('site_name', env('APP_NAME', 'Yasuda Jaya Tour'));
      $blueprint->add('site_font', 'Poppins');
      $blueprint->add('site_spa', false);
      $blueprint->add('site_logoHeight', 35);
      $blueprint->add('site_maxContentWidth', MaxWidth::Full->value);
      $blueprint->add('site_navigation', false);
      $blueprint->add('table_striped', false);
      $blueprint->add('table_actionPosition', ActionsPosition::BeforeColumns->name);
      $blueprint->add('color_primary', 'rgb(216, 36, 49)');
      $blueprint->add('color_secondary', 'rgb(100, 38, 110)');
    });
  }
};

