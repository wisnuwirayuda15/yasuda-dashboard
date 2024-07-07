<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Event;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\MeetingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MeetingResource\RelationManagers;

class MeetingResource extends Resource
{
  protected static ?string $model = Event::class;

  protected static ?string $modelLabel = 'Event';

  protected static ?string $navigationIcon = 'gmdi-event';

  protected static ?int $navigationSort = 1;

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::OTHER->getLabel();
  }

  public static function form(Form $form): Form
  {
    return $form->schema(static::getFormSchema());
  }

  public static function getFormSchema(): array
  {
    return [
      TextInput::make('title')
        ->required()
        ->maxLength(100)
        ->columnSpanFull()
        ->label('Judul'),
      DateTimePicker::make('date')
        ->required()
        ->label('Tanggal')
        ->columnSpanFull()
        ->default(today()),
      RichEditor::make('description')
        ->columnSpanFull()
        ->label('Deskripsi'),
    ];
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('title')
          ->searchable(),
        TextColumn::make('date')
          ->dateTime()
          ->sortable(),
        TextColumn::make('description')
          ->limit(40)
          ->placeholder('No content')
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
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ])
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
