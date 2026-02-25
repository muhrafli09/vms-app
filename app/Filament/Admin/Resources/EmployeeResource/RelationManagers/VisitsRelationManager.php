<?php

namespace App\Filament\Admin\Resources\EmployeeResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\RelationManagers\RelationManager;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('visitor')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('visitor.name')
                    ->label('Visitor'),
                Tables\Columns\TextColumn::make('visitor.phone')
                    ->label('Phone'),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=V&color=7F9CF5&background=EBF4FF'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Type')
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'completed',
                        'gray' => 'checked_in',
                    ]),
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
                TextEntry::make('visitor.name')
                    ->label('Visitor Name'),
                TextEntry::make('visitor.email')
                    ->label('Email'),
                TextEntry::make('visitor.phone')
                    ->label('Phone Number'),
                TextEntry::make('visitor.company')
                    ->label('Company'),
                TextEntry::make('purpose')
                    ->label('Purpose for Visit'),
                TextEntry::make('status')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'scheduled' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                TextEntry::make('created_at')
                    ->dateTime(),
            ])
            ->columns(1)
            ->inlineLabel();
    }
}
