<?php

use App\Enums\NavigationGroupLabel;

return [
  /*
  |--------------------------------------------------------------------------
  | Table Columns
  |--------------------------------------------------------------------------
  */

  'column.name' => 'Nama',
  'column.guard_name' => 'Nama Penjaga',
  'column.roles' => 'Peran',
  'column.permissions' => 'Izin',
  'column.updated_at' => 'Dirubah',

  /*
  |--------------------------------------------------------------------------
  | Form Fields
  |--------------------------------------------------------------------------
  */

  'field.name' => 'Nama',
  'field.guard_name' => 'Nama Penjaga',
  'field.permissions' => 'Izin',
  'field.select_all.name' => 'Pilih Semua',
  'field.select_all.message' => 'Aktifkan semua izin yang <strong>Tersedia</strong> untuk Peran ini.',

  /*
  |--------------------------------------------------------------------------
  | Navigation & Resource
  |--------------------------------------------------------------------------
  */

  'nav.group' => NavigationGroupLabel::SETTING->getLabel(),
  'nav.role.label' => 'Peran',
  'nav.role.icon' => 'heroicon-s-shield-check',
  'resource.label.role' => 'Peran',
  'resource.label.roles' => 'Peran',

  /*
  |--------------------------------------------------------------------------
  | Section & Tabs
  |--------------------------------------------------------------------------
  */

  'section' => 'Entitas',
  'resources' => 'Sumber Daya',
  'widgets' => 'Widget',
  'pages' => 'Halaman',
  'custom' => 'Izin Kustom',

  /*
  |--------------------------------------------------------------------------
  | Messages
  |--------------------------------------------------------------------------
  */

  'forbidden' => 'Kamu tidak punya izin akses',

  /*
  |--------------------------------------------------------------------------
  | Resource Permissions' Labels
  |--------------------------------------------------------------------------
  */

  'resource_permission_prefixes_labels' => [
    'view' => 'Lihat',
    'view_any' => 'Lihat Semua',
    'create' => 'Buat',
    'update' => 'Perbarui',
    'delete' => 'Hapus',
    'delete_any' => 'Hapus Semua',
    'force_delete' => 'Hapus Permanen',
    'force_delete_any' => 'Hapus Permanen Semua',
    'restore' => 'Pulihkan',
    'replicate' => 'Duplikat',
    'reorder' => 'Urutkan',
    'restore_any' => 'Pulihkan Semua',
  ],
];
