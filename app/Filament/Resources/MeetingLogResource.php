<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingLogResource\Pages;
use App\Filament\Resources\MeetingLogResource\RelationManagers;
use App\Models\Meeting;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class MeetingLogResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static string | BackedEnum | null $navigationIcon  = 'heroicon-o-document-text';
    protected static string | UnitEnum  | null $navigationGroup  = 'Video Meetings';
    protected static ?int    $navigationSort                     = 3;
    protected static ?string $navigationLabel                    = 'Meeting Logs';
    protected static ?string $modelLabel                         = 'Meeting Log';
    protected static ?string $pluralModelLabel                   = 'Meeting Logs';
    protected static ?string $slug                               = 'meeting-logs';

    public static function canCreate(): bool              { return false; }
    public static function canEdit(Model $record): bool   { return false; }
    public static function canDelete(Model $record): bool { return false; }

    // ── Infolist (view page header) ────────────────────────────────────────────

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Meeting Info')
                ->schema([
                    TextEntry::make('meeting_code')
                        ->label('Meeting Code')
                        ->fontFamily(FontFamily::Mono)
                        ->copyable(),

                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state) => match ($state) {
                            'waiting' => 'warning',
                            'active'  => 'success',
                            'ended'   => 'danger',
                            default   => 'gray',
                        }),

                    TextEntry::make('host_peer_id')
                        ->label('Host Peer ID')
                        ->fontFamily(FontFamily::Mono)
                        ->placeholder('—')
                        ->copyable(),

                    TextEntry::make('created_at')
                        ->label('Started At')
                        ->dateTime('M j, Y · H:i:s', timezone: 'Asia/Manila'),
                ])
                ->columns(2),
        ]);
    }

    // ── Table (list view) ──────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meeting_code')
                    ->label('Meeting Code')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono)
                    ->copyable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'waiting' => 'warning',
                        'active'  => 'success',
                        'ended'   => 'danger',
                        default   => 'gray',
                    }),

                TextColumn::make('chats_count')
                    ->label('Chat Messages')
                    ->counts('chats')
                    ->badge()
                    ->color('info'),

                TextColumn::make('frontend_logs_count')
                    ->label('Events')
                    ->counts('frontendLogs')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Started At')
                    ->dateTime('M j, Y · H:i', timezone: 'Asia/Manila')
                    ->timezone('Asia/Manila')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'active'  => 'Active',
                        'ended'   => 'Ended',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    // ── Relations ──────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [
            RelationManagers\RecordingsRelationManager::class,
            RelationManagers\ChatsRelationManager::class,
            RelationManagers\ActivityRelationManager::class,
        ];
    }

    // ── Pages ──────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeetingLogs::route('/'),
            'view'  => Pages\ViewMeetingLog::route('/{record}'),
        ];
    }
}
