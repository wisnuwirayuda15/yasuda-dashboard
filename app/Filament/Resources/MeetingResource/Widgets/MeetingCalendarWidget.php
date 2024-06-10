<?php

namespace App\Filament\Resources\MeetingResource\Widgets;

use App\Models\Meeting;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\MeetingResource;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class MeetingCalendarWidget extends FullCalendarWidget
{
  public Model|string|null $model = Meeting::class;

  public function fetchEvents(array $fetchInfo): array
  {
    return Meeting::query()
      ->where('date', '>=', $fetchInfo['start'])
      ->get()
      ->map(fn(Meeting $meeting) => EventData::make()
        ->id($meeting->id)
        ->title($meeting->title)
        ->start($meeting->date))
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
        ->label('Tambah Jadwal Meeting')
        ->icon(MeetingResource::getNavigationIcon())
        ->mountUsing(
          function (Form $form, array $arguments) {
            $form->fill([
              'date' => $arguments['start'] ?? null
            ]);
          }
        )
    ];
  }

  protected function modalActions(): array
  {
    return [
      EditAction::make()
        ->mountUsing(function (Meeting $record, Form $form, array $arguments, EditAction $component) {
          if (filled($arguments)) {
            $component->modal(false);
          }
          $form->fill([
            'title' => $record->title,
            'date' => $arguments['event']['start'] ?? $record->date,
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
