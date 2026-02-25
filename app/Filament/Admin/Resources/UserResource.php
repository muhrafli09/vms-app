<?php

namespace App\Filament\Admin\Resources;

use App\Models\{
    User
};
use Filament\{
    Forms,
    Tables,
    Infolists,
    Pages\Page,
    Forms\Form,
    Tables\Table,
    Resources\Resource,
};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique(User::class, ignoreRecord: true)
                    ->required()
                    ->autofocus()
                    ->disableAutocomplete(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->label(fn (Page $livewire): string => ($livewire instanceof Pages\EditUser) ? 'New Password' : 'Password')
                    ->dehydrated(fn (null|string $state): bool => filled($state))
                    ->required(fn (Page $livewire) => ($livewire instanceof Pages\CreateUser))
                    ->autofocus()
                    ->placeholder('Password')
                    ->disableAutocomplete()
                    ->minLength(8)
                    ->maxLength(20),
                Forms\Components\Toggle::make('is_admin')
                    ->required()
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address'),
                Tables\Columns\IconColumn::make('is_admin')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, User $record) {
                        // Prevent deleting user if they have an employee record
                        if ($record->employee) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Cannot delete user')
                                ->body('This user is assigned to an employee record. Please delete or reassign the employee first.')
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            // Check if any user has employee record
                            $usersWithEmployee = $records->filter(fn ($user) => $user->employee !== null);
                            
                            if ($usersWithEmployee->count() > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Cannot delete users')
                                    ->body($usersWithEmployee->count() . ' user(s) are assigned to employee records. Please delete or reassign the employees first.')
                                    ->persistent()
                                    ->send();
                                
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }    
}
