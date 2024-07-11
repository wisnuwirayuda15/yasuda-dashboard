<?php

namespace App\Filament\Resources\MeetingResource\Pages;

use App\Filament\Resources\MeetingResource;
use App\Filament\Resources\MeetingResource\Widgets\MeetingCalendarWidget;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMeetings extends ManageRecords
{
  protected static string $resource = MeetingResource::class;

  protected function getHeaderActions(): array
  {
    return [
      // Actions\CreateAction::make(),
    ];
  }

  protected function getHeaderWidgets(): array
  {
    return [
      MeetingCalendarWidget::class,
    ];
  }

  public function setPage($page, $pageName = 'page'): void
  {
    parent::setPage($page, $pageName);
    $this->dispatch(\App\Enums\JavascriptEvent::SCROLL_TO_TOP->value);
  }
}
