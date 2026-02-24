<?php

namespace App\Filament\Admin\Resources;

use App\Models\Kiosk;
use Filament\{Forms, Tables, Resources\Resource};
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Admin\Resources\KioskResource\Pages;

class KioskResource extends Resource
{
    protected static ?string $model = Kiosk::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';

    protected static ?string $slug = 'manage/kiosks';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Kiosk Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->label('Location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('token')
                    ->label('Access Token')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Auto-generated on creation'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Kiosk Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('viewUrl')
                    ->label('Kiosk URL')
                    ->icon('heroicon-o-link')
                    ->modalHeading('Kiosk Access URL')
                    ->modalContent(fn ($record) => view('filament.admin.kiosk-url-modal', ['kiosk' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
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
            'index' => Pages\ManageKiosks::route('/'),
        ];
    }    
}
