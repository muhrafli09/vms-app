<?php

namespace App\Filament\Admin\Resources\VisitorResource\RelationManagers;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\RelationManagers\RelationManager;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('employee.full_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Host'),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->state(function (Model $record) {
                        return $record->arrival ? Carbon::parse($record->arrival)->format('Y-m-d') : '-';
                    }),
                Tables\Columns\TextColumn::make('arrival')
                    ->label('Arrival')
                    ->formatStateUsing(function (Model $record) {
                        return $record->arrival ? Carbon::parse($record->arrival)->format('H:i:sa') : '-';
                    }),
                Tables\Columns\TextColumn::make('departure')
                    ->label('Departure')
                    ->formatStateUsing(function (Model $record) {
                        return $record->departure ? Carbon::parse($record->departure)->format('H:i:sa') : '-';
                    }),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Purpose')
                    ->limit(30),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('employee.full_name')
                    ->label('Host'),
                TextEntry::make('employee.department.name')
                    ->label('Department'),
                TextEntry::make('purpose')
                    ->label('Purpose for Visit'),
                TextEntry::make('arrival')
                    ->label('Arrival Time')
                    ->dateTime(),
                TextEntry::make('departure')
                    ->label('Departure Time')
                    ->dateTime(),
            ])
            ->columns(1)
            ->inlineLabel();
    }
}
