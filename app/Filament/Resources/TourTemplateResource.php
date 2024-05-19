<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Regency;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Models\TourTemplate;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TourTemplateResource\Pages;
use App\Filament\Resources\TourTemplateResource\RelationManagers;

class TourTemplateResource extends Resource
{
  protected static ?string $model = TourTemplate::class;

  protected static ?string $navigationIcon = 'eos-templates';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('name')
          ->required()
          ->live()
          ->helperText('You can generate name based on selected regency and destinations.')
          ->maxLength(255)
          ->hintAction(
            Forms\Components\Actions\Action::make('generate_name')
              ->action(fn(Get $get, Set $set) => self::setTourTemplateName($get, $set))
          ),
        Forms\Components\Select::make('regency_id')
          ->required()
          ->relationship('regency', 'name'),
        Forms\Components\Select::make('destinations')
          ->required()
          ->multiple()
          ->options(Destination::pluck('name', 'id')),
      ])->columns(1);
  }

  public static function setTourTemplateName(Get $get, Set $set): void
  {
    $regency = Regency::find($get('regency_id'))?->name;
    $destinations = Destination::find($get('destinations'));
    if (blank($regency) || blank($destinations)) {
      $set('name', null);
    } else {
      $set('name', "{$regency} ({$destinations->implode('name', ' + ')})");
    }
  }

  public static function table(Table $table): Table
  {
    $destination = Destination::all();

    return $table
      ->columns([
        Tables\Columns\TextColumn::make('name'),
        Tables\Columns\TextColumn::make('regency.name')
          ->sortable(),
        Tables\Columns\TextColumn::make('destinations')
          ->badge()
          ->formatStateUsing(fn(string $state): string => $destination->find($state)->name),
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
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageTourTemplates::route('/'),
    ];
  }
}
