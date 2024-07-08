<?php

use App\Filament\Resources;
use App\Enums\NavigationGroupLabel;

return [
  'group' => [
    NavigationGroupLabel::OPERATIONAL->value => 'Operasional & Logistik',
    NavigationGroupLabel::FINANCE->value => 'Keuangan',
    NavigationGroupLabel::MARKETING->value => 'Penjualan & Pemasaran',
    NavigationGroupLabel::MASTER_DATA->value => 'Data Utama',
    NavigationGroupLabel::SETTING->value => 'Pengaturan',
    NavigationGroupLabel::HR->value => 'Manajemen Karyawan',
    NavigationGroupLabel::OTHER->value => 'Lain-Lain',
  ],
  'label' => [
    Resources\CustomerResource::getSlug() => 'Pelanggan',
    Resources\DestinationResource::getSlug() => 'Tujuan Wisata',
    Resources\EmployeeResource::getSlug() => 'Karyawan',
    Resources\FleetResource::getSlug() => 'Armada',
    Resources\InvoiceResource::getSlug() => 'Faktur',
    Resources\LoyaltyPointResource::getSlug() => 'Poin Loyalitas',
    Resources\MeetingResource::getSlug() => 'Event',
    Resources\OrderResource::getSlug() => 'Pesanan',
    Resources\OrderFleetResource::getSlug() => 'Ketersediaan Armada',
    Resources\ProfitLossResource::getSlug() => 'Keuntungan & Kerugian',
    Resources\RewardResource::getSlug() => 'Hadiah',
    Resources\SalesVisitResource::getSlug() => 'Kunjungan Customer',
    Resources\ShirtResource::getSlug() => 'Baju Wisata',
    Resources\TourReportResource::getSlug() => 'Laporan Wisata',
    Resources\TourTemplateResource::getSlug() => 'Template Wisata',
    Resources\UserResource::getSlug() => 'Akun User',
  ]
];
