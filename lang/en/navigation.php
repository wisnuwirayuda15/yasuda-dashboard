<?php

use App\Filament\Resources;
use App\Enums\NavigationGroupLabel;

return [
  'group' => [
    NavigationGroupLabel::OPERATIONAL->value => 'Operational & Logistic',
    NavigationGroupLabel::FINANCE->value => 'Finance',
    NavigationGroupLabel::MARKETING->value => 'Sales & Marketing',
    NavigationGroupLabel::MASTER_DATA->value => 'Master Data',
    NavigationGroupLabel::SETTING->value => 'Settings',
    NavigationGroupLabel::HR->value => 'Human Resource',
    NavigationGroupLabel::SYSTEM->value => 'System',
    NavigationGroupLabel::OTHER->value => 'Other',
  ],
  'label' => [
    Resources\CustomerResource::getSlug() => 'Customer',
    Resources\DestinationResource::getSlug() => 'Destination',
    Resources\EmployeeResource::getSlug() => 'Employee',
    Resources\FleetResource::getSlug() => 'Fleet',
    Resources\InvoiceResource::getSlug() => 'Invoice',
    Resources\LoyaltyPointResource::getSlug() => 'Loyalty Points',
    Resources\MeetingResource::getSlug() => 'Event',
    Resources\OrderResource::getSlug() => 'Order',
    Resources\OrderFleetResource::getSlug() => 'Fleet Availability',
    Resources\ProfitLossResource::getSlug() => 'Profit & Loss',
    Resources\RewardResource::getSlug() => 'Reward',
    Resources\SalesVisitResource::getSlug() => 'Sales Visit',
    Resources\ShirtResource::getSlug() => 'Tour Shirt',
    Resources\TourReportResource::getSlug() => 'Tour Report',
    Resources\TourTemplateResource::getSlug() => 'Tour Template',
    Resources\UserResource::getSlug() => 'User Account',
    'pulse' => 'Website Analytics'
  ]
];