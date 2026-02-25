<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AppointmentResource\Pages;
use App\Models\{Visit, Employee};
use Filament\{Forms, Tables, Resources\Resource};
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AppointmentResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Appointments';
    
    protected static ?string $modelLabel = 'Appointment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visitor Information')
                    ->schema([
                        Forms\Components\TextInput::make('visitor_name')
                            ->label('Visitor Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('visitor_phone')
                            ->label('Phone')
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
                        Forms\Components\TextInput::make('visitor_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('visitor_company')
                            ->label('Company')
                            ->maxLength(255),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Appointment Details')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Meeting with')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                $record->full_name . ' - ' . 
                                ($record->designation ? $record->designation->name : 'No Designation') . ', ' . 
                                ($record->department ? $record->department->name : 'No Department')
                            )
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('scheduled_time')
                            ->label('Scheduled Time')
                            ->required()
                            ->native(false)
                            ->minDate(now()),
                        Forms\Components\Textarea::make('purpose')
                            ->label('Purpose of Visit')
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
                    ->label('Visitor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visitor.phone')
                    ->label('Phone'),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Meeting with')
                    ->description(fn (Visit $record): string => 
                        ($record->employee->designation ? $record->employee->designation->name : 'No Designation') . ', ' . 
                        ($record->employee->department ? $record->employee->department->name : 'No Department')
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('scheduled_time')
                    ->label('Scheduled')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'completed',
                    ]),
            ])
            ->defaultSort('scheduled_time', 'asc')
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('qrCode')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->modalHeading('Appointment QR Code')
                    ->modalContent(fn ($record) => view('filament.app.qr-code-modal', ['visit' => $record]))
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_time');
            
        if (Auth::check()) {
            $query->where('created_by', Auth::id());
        }
        
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
