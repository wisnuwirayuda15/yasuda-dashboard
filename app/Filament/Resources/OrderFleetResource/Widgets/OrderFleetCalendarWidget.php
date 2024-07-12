<?php

namespace App\Filament\Resources\OrderFleetResource\Widgets;

use App\Models\OrderFleet;
use Filament\Widgets\Widget;
use App\Filament\Resources\OrderFleetResource;
use Saade\FilamentFullCalendar\Data\EventData;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class OrderFleetCalendarWidget extends FullCalendarWidget
{
  use HasWidgetShield;
  
  public function fetchEvents(array $fetchInfo): array
  {
    return OrderFleet::query()
      ->where('trip_date', '>=', $fetchInfo['start'])
      ->get()->map(
        function (OrderFleet $record) {
          $status = $record->getStatus();

          $title = "{$record->code} ({$status})";

          return EventData::make()
            ->id($record->id)
            ->title($title)
            ->start($record->trip_date)
            ->url(OrderFleetResource::getUrl('view', ['record' => $record]), true);
        }
      )
      ->toArray();
  }

  public function config(): array
  {
    return [
      'editable' => false,
      'selectable' => false,
      'customButtons' => [
        'modelText' => [
          'text' => strtoupper(__('navigation.label.' . OrderFleetResource::getSlug())),
        ]
      ],
      'headerToolbar' => [
        'left' => 'title',
        'center' => 'prev,modelText,next',
        'right' => 'dayGridMonth,dayGridWeek,dayGridDay,today',
      ],
    ];
  }

  protected function headerActions(): array
  {
    return [];
  }

  protected function modalActions(): array
  {
    return [];
  }

  public function eventDidMount(): string
  {
    return <<<JS
      function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
        el.setAttribute("x-tooltip", "tooltip");
        el.setAttribute("x-data", "{ tooltip: '" + event.title + "' }");
      }
    JS;
  }
}
