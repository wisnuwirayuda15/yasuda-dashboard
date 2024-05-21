<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum DestinationType: string implements HasLabel, HasColor, HasIcon, HasDescription
{
  case SISWA_ONLY = 'AA';
  case SISWA_DEWASA = 'AO';
  case SISWA_DEWASA_PEMBINA = 'LL';
  case SISWA_TAMBAHAN = 'AR';
  case DEWASA = 'DO';

  public function getLabel(): ?string
  {
    return match ($this) {
      self::SISWA_ONLY => 'Siswa Only',
      self::SISWA_DEWASA => 'Siswa & Dewasa',
      self::SISWA_DEWASA_PEMBINA => 'Siswa, Dewasa, Pembina',
      self::SISWA_TAMBAHAN => 'Siswa, Tambahan',
      self::DEWASA => 'Dewasa',
    };
  }

  public function getDescription(): string|null
  {
    return match ($this) {
      self::SISWA_ONLY => 'Paket Anak',
      self::SISWA_DEWASA => 'Paket Anak x 2 + Tambahan',
      self::SISWA_DEWASA_PEMBINA => 'Paket Anak x 2 + Tambahan + Pembina',
      self::SISWA_TAMBAHAN => 'Paket Anak + Tambahan',
      self::DEWASA => 'Dewasa only',
    };
  }

  public function getColor(): string|array|null
  {
    return match ($this) {
      self::SISWA_ONLY => 'info',
      self::SISWA_DEWASA => 'success',
      self::SISWA_DEWASA_PEMBINA => 'warning',
      self::SISWA_TAMBAHAN => 'danger',
      self::DEWASA => 'primary',
    };
  }

  public function getIcon(): ?string
  {
    return match ($this) {
      self::SISWA_ONLY => 'heroicon-s-user',
      self::SISWA_DEWASA => 'heroicon-s-users',
      self::SISWA_DEWASA_PEMBINA => 'heroicon-s-user-group',
      self::SISWA_TAMBAHAN => 'heroicon-s-user-plus',
      self::DEWASA => 'gmdi-man',
    };
  }
}
