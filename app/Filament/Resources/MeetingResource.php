<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Meeting;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MeetingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MeetingResource\RelationManagers;

class MeetingResource extends Resource
{
  protected static ?string $model = Meeting::class;

  protected static ?string $navigationIcon = 'fluentui-device-meeting-room-remote-20';

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
        Tables\Columns\TextColumn::make('title')
          ->searchable(),
        Tables\Columns\TextColumn::make('date')
          ->dateTime()
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
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
