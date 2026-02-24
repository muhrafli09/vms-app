<?php

namespace App\Filament\Admin\Resources;

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

use App\Filament\Admin\{
    Resources\VisitResource\Pages,
    Resources\VisitResource\RelationManagers,
};

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Visit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visitor Information')
                    ->schema([
                        Forms\Components\TextInput::make('visitor_phone')
                            ->label('Phone')
                            ->placeholder('Enter phone number')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $visitor = \App\Models\Visitor::where('phone', $state)->first();
                                    if ($visitor) {
                                        $set('visitor_name', $visitor->name);
                                        $set('visitor_email', $visitor->email);
                                        $set('visitor_company', $visitor->company);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('visitor_name')
                            ->label('Name')
                            ->placeholder('Visitor name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('visitor_email')
                            ->label('Email')
                            ->placeholder('visitor@email.com')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('visitor_company')
                            ->label('Company')
                            ->placeholder('Company name')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Visit Details')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Host')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable(['first_name', 'last_name'])
                            ->required()
                            ->preload(),
                        Forms\Components\ViewField::make('photo')
                            ->label('Photo')
                            ->view('forms.components.camera-field'),
                        Forms\Components\Textarea::make('purpose')
                            ->label('Purpose')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('visitor', 'employee'))
            ->columns([
                Tables\Columns\TextColumn::make('visitor.name')
                    ->label(__('Visitor'))
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visitor.phone')
                    ->label(__('Phone'))
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visitor.company')
                    ->label(__('Company'))
                    ->default('-')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('photo')
                    ->label(__('Photo'))
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=V&color=7F9CF5&background=EBF4FF'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'completed',
                        'gray' => fn ($state) => $state === null,
                    ])
                    ->formatStateUsing(fn ($state) => $state ?? 'walk-in'),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
                TextEntry::make('status')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'Walk-in'),
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
