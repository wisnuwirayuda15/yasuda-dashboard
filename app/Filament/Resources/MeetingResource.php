<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Event;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MeetingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MeetingResource\RelationManagers;
use Illuminate\Support\HtmlString;

class MeetingResource extends Resource
{
  protected static ?string $model = Event::class;

  protected static ?string $modelLabel = 'Event';

  protected static ?string $navigationIcon = 'gmdi-event';

  public static function form(Form $form): Form
  {
    return $form->schema(static::getFormSchema());
  }

  public static function getFormSchema(): array
  {
    return [
      Forms\Components\TextInput::make('title')
        ->required()
        ->maxLength(100)
        ->columnSpanFull()
        ->label('Judul'),
      Forms\Components\DateTimePicker::make('date')
        ->required()
        ->columnSpanFull()
        ->label('Tanggal'),
      Forms\Components\RichEditor::make('description')
        ->columnSpanFull()
        ->label('Deskripsi'),
    ];
  }

  public static function table(Table $table): Table
  {
    //TODO: hide the table

    return $table
      ->columns([
        TextColumn::make('title')
          ->searchable(),
        TextColumn::make('date')
          ->dateTime()
          ->sortable(),
        TextColumn::make('description')
          ->limit(40)
          ->html(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
    ;
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageMeetings::route('/'),
    ];
  }
}
