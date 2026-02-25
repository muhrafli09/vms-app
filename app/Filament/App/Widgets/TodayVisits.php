<?php

namespace App\Filament\App\Widgets;

use Carbon\Carbon;
use App\Models\{
    Visit
};
use Filament\{
    Forms,
    Tables,
    Tables\Table,
    Tables\Actions\Action,
    Tables\Actions\CreateAction,
};
use Illuminate\Database\Eloquent\{
    Model,
    Builder,
};
use Filament\Notifications\Notification;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Mail;
use App\Mail\HostVisitorCheckinMail;

class TodayVisits extends BaseWidget
{

    public function table(Table $table): Table
    {
        return $table
        ->query(Visit::query()->with('visitor', 'employee')->whereDate('created_at', Carbon::today()))
        ->defaultSort('created_at', 'desc')
        ->columns([
            Tables\Columns\TextColumn::make('visitor.name')
                ->label(__('Visitor')),
            Tables\Columns\TextColumn::make('employee.full_name')
                ->label(__('Host'))
                ->description(fn (Visit $record): string => 
                    ($record->employee->designation ? $record->employee->designation->name : 'N/A') . ', ' . 
                    ($record->employee->department ? $record->employee->department->name : 'N/A')
                ),
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
        ->paginated(false)
        ->actions([
            Action::make('markDepart')
                ->requiresConfirmation()
                ->action(function (Visit $record) {
                    $record->departure = now();
                    $record->save();
                })
                ->hidden(fn (Visit $record): bool => $record->departure !== null),
        ])
        ->headerActions([
            CreateAction::make()
                ->form([
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
                                ->getOptionLabelFromRecordUsing(fn ($record) => 
                                    $record->full_name . ' - ' . 
                                    ($record->designation ? $record->designation->name : 'No Designation') . ', ' . 
                                    ($record->department ? $record->department->name : 'No Department')
                                )
                                ->searchable(['first_name', 'last_name', 'email'])
                                ->required()
                                ->preload(),
                            Forms\Components\ViewField::make('photo')
                                ->label('Photo')
                                ->view('forms.components.camera-field')
                                ->required(),
                            Forms\Components\Textarea::make('purpose')
                                ->label('Purpose')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    // Validate photo is captured
                    if (empty($data['photo'])) {
                        throw new \Exception('Photo is required. Please capture visitor photo.');
                    }
                    
                    $visitor = \App\Models\Visitor::updateOrCreate(
                        ['phone' => $data['visitor_phone']],
                        [
                            'name' => $data['visitor_name'],
                            'email' => $data['visitor_email'] ?? null,
                            'company' => $data['visitor_company'] ?? null,
                        ]
                    );
                    
                    unset($data['visitor_phone'], $data['visitor_name'], $data['visitor_email'], $data['visitor_company']);
                    
                    // Save photo from base64
                    if (!empty($data['photo']) && str_starts_with($data['photo'], 'data:image')) {
                        $image = $data['photo'];
                        $image = str_replace('data:image/jpeg;base64,', '', $image);
                        $image = str_replace(' ', '+', $image);
                        $imageName = 'visits/' . uniqid() . '.jpg';
                        \Storage::disk('public')->put($imageName, base64_decode($image));
                        $data['photo'] = $imageName;
                    } else {
                        unset($data['photo']);
                    }
                    
                    $data['visitor_id'] = $visitor->id;
                    $data['uuid'] = \Illuminate\Support\Str::uuid();
                    $data['status'] = 'checked_in';
                    $data['arrival'] = now();
                    
                    return $data;
                })
                ->successNotification(
                    Notification::make()
                         ->success()
                         ->title('Visitor Registered')
                         ->body('You have successfully created a new visitor'),
                 )
                 ->after(function ($record) {
                     // Send email notification to host
                     $record->load('visitor', 'employee.user');
                     
                     if ($record->employee->user && $record->employee->user->email) {
                         try {
                             Mail::to($record->employee->user->email)->send(new HostVisitorCheckinMail($record));
                         } catch (\Exception $e) {
                             \Log::error('Failed to send host notification: ' . $e->getMessage());
                         }
                     }
                 }),
        ]);
    }
}
