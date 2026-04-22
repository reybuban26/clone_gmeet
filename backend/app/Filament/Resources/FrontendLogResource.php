<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FrontendLogResource\Pages;
use App\Models\FrontendLog;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FrontendLogResource extends Resource
{
    protected static ?string $model = FrontendLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Video Meetings';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Frontend Log';

    protected static ?string $pluralLabel = 'Frontend Logs';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('meeting_code')
                ->label('Meeting Code')
                ->disabled(),

            TextInput::make('user_id')
                ->label('User ID')
                ->disabled(),

            TextInput::make('action')
                ->disabled()
                ->columnSpanFull(),

            KeyValue::make('metadata')
                ->disabled()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('meeting_code')
                    ->label('Meeting Code')
                    ->searchable()
                    ->fontFamily('mono')
                    ->placeholder('—'),

                TextColumn::make('action')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('metadata')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state) : '—')
                    ->limit(60)
                    ->tooltip(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : null),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('has_meeting_code')
                    ->label('Has Meeting Code')
                    ->query(fn (Builder $query) => $query->whereNotNull('meeting_code')),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFrontendLogs::route('/'),
            'view'  => Pages\ViewFrontendLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
