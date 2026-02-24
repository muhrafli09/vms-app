<?php

namespace App\Filament\App\Resources;

use Carbon\{
    Carbon,
};

use App\Models\{
    Visit
};

use Filament\{
    Forms,
    Tables,
    Forms\Form,
    Tables\Table,
    Infolists\Infolist,
    Resources\Resource,
    Tables\Actions\Action,
    Tables\Actions\CreateAction,
    Infolists\Components\IconEntry,
    Infolists\Components\TextEntry,
};

use Illuminate\{
    Database\Eloquent\Model,
    Database\Eloquent\Builder,
    Database\Eloquent\SoftDeletingScope,
};

use App\Filament\App\Resources\VisitResource\Pages;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $slug = 'manage/visits';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('visitor', 'employee'))
            ->columns([
                Tables\Columns\TextColumn::make('visitor.name')
                    ->label(__('Visitor'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('visitor.phone')
                    ->label(__('Phone'))
                    ->searchable(),
                Tables\Columns\ImageColumn::make('photo')
                    ->label(__('Photo'))
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=V&color=7F9CF5&background=EBF4FF'),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label(__('Host'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->state(function (Model $record) {
                        return $record->arrival ? Carbon::parse($record->arrival)->format('Y-m-d') : '-';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('arrival')
                    ->label('Arrival')
                    ->formatStateUsing(function (Model $record) {
                        return $record->arrival ? Carbon::parse($record->arrival)->format('H:i:sa') : '-';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('departure')
                    ->label('Departure')
                    ->formatStateUsing(function (Model $record) {
                        return $record->departure ? Carbon::parse($record->departure)->format('H:i:sa') : '-';
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn ($state): string => 'Jan 01, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Visits from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Visits until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Action::make('markDepart')
                    ->requiresConfirmation()
                    ->action(function (Visit $record) {
                        $record->departure = now();
                        $record->save();
                    })
                    ->hidden(fn (Visit $record): bool => $record->departure !== null),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
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
                TextEntry::make('photo')
                    ->label('Photo')
                    ->formatStateUsing(fn ($state) => $state ? asset('storage/' . $state) : '-'),
                TextEntry::make('purpose')
                    ->label('Purpose for Visit'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ])
            ->columns(1)
            ->inlineLabel();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVisits::route('/'),
        ];
    }
}
