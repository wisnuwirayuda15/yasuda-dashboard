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
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TourTemplateResource\Pages;
use App\Filament\Resources\TourTemplateResource\RelationManagers;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;

class TourTemplateResource extends Resource
{
  protected static ?string $model = TourTemplate::class;

  protected static ?string $navigationIcon = 'fontisto-holiday-village';

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MASTER_DATA->getLabel();
  }

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        TextInput::make('name')
          ->required()
          ->live(true)
          ->helperText('You can generate name based on selected regency and destinations.')
          ->maxLength(255)
          ->hintAction(
            Action::make('generate_name')
              ->disabled(fn(Get $get) => blank($get('regency_id')) || blank($get('destinations')))
              ->action(function (Get $get, Set $set, TextInput $component) {
                $regency = Regency::findOrFail($get('regency_id'));
                $destinations = Destination::findOrFail($get('destinations'));
                if (blank($regency) || blank($destinations)) {
                  $set($component, null);
                } else {
                  $set($component, "{$regency->name} ({$destinations->implode('name', ' + ')})");
                }
              })
          ),
        Select::make('regency_id')
          ->required()
          ->live(true)
          ->relationship('regency', 'name'),
        Select::make('destinations')
          ->required()
          ->live(true)
          ->multiple()
          ->options(Destination::pluck('name', 'id')),
        FileUpload::make('image')
          ->image()
          ->imageEditor()
          ->maxSize(2048)
          ->directory('tour-template')
          ->imageCropAspectRatio('1:1')
          ->imageResizeMode('cover')
          ->columnSpanFull(),
        RichEditor::make('description')
          ->columnSpanFull(),
      ])->columns(1);
  }

  public static function table(Table $table): Table
  {
    $destination = Destination::all();

    return $table
      ->columns([
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('regency.name')
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('destinations')
          ->badge()
          ->searchable()
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
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageTourTemplates::route('/'),
    ];
  }
}
