<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FrontendLogResource\Pages;
use App\Models\FrontendLog;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FrontendLogResource extends Resource
{
    protected static ?string $model = FrontendLog::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | \UnitEnum | null $navigationGroup = 'Video Meetings';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Frontend Log';

    protected static ?string $pluralModelLabel = 'Frontend Logs';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('meeting_code')->label('Meeting Code')->disabled(),
            TextInput::make('user_id')->label('User ID')->disabled(),
            TextInput::make('action')->disabled()->columnSpanFull(),
            KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),

                TextColumn::make('meeting_code')
                    ->label('Meeting Code')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono)
                    ->placeholder('—'),

                TextColumn::make('action')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('metadata')
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state) : '—')
                    ->limit(60),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('has_meeting_code')
                    ->label('Has Meeting Code')
                    ->query(fn (Builder $query) => $query->whereNotNull('meeting_code')),
            ])
            ->recordActions([
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
