<?php

namespace App\Filament\Resources\RoomResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\Log;

class BookedGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookedGroups';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('start_date')
                ->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()->sortable(),
                ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->preloadRecordSelect()->form(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    DateTimePicker::make('start_date')->required(),
                    DateTimePicker::make('end_date')->required(),
                ]),
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
