<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TourReportResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class TourReportRelationManager extends RelationManager
{
  protected static string $relationship = 'tourReport';

  public function form(Form $form): Form
  {
    return TourReportResource::form($form);
  }

  public function table(Table $table): Table
  {
    return TourReportResource::table($table);
  }
}
