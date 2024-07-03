<?php

namespace App\Filament\Resources\MeetingResource\Widgets;

use App\Models\Event;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\MeetingResource;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Actions\ViewAction;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class MeetingCalendarWidget extends FullCalendarWidget
{
  public Model|string|null $model = Event::class;

  public function fetchEvents(array $fetchInfo): array
  {
    return Event::query()
      ->where('date', '>=', $fetchInfo['start'])
      ->get()->map(
        fn(Event $event) => EventData::make()
          ->id($event->id)
          ->title($event->title)
          ->start($event->date)
      )
      ->toArray();
  }

  public function getFormSchema(): array
  {
    return MeetingResource::getFormSchema();
  }

  protected function headerActions(): array
  {
    return [
      CreateAction::make()
        ->label('Event')
        ->icon('heroicon-o-plus')
        ->color('success')
        ->tooltip('Tambah Event')
        ->mountUsing(function (Form $form, array $arguments) {
          $form->fill([
            'date' => $arguments['start'] ?? null
          ]);
        })
    ];
  }

  protected function modalActions(): array
  {
    return [
      ViewAction::make(),
      EditAction::make()
        ->mountUsing(function (Event $record, Form $form, array $arguments, EditAction $component) {
          if (filled($arguments)) {
            $component->modal(false);
          }
          $form->fill([
            'title' => $record->title,
            'date' => $arguments['event']['start'] ?? $record->date,
            'description' => $record->description,
          ]);
        }),
      DeleteAction::make()
        ->icon('heroicon-s-trash'),
    ];
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
