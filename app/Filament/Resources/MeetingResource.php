<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingResource\Pages;
use App\Models\Meeting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

// TAMA AT EXACT V4 IMPORTS: Wala nang "Tables" sa gitna!
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-video-camera';

    protected static string | \UnitEnum | null $navigationGroup = 'Video Meetings';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('meeting_code')
                ->label('Meeting Code')
                ->disabled()
                ->columnSpanFull(),

            TextInput::make('host_peer_id')
                ->label('Host Peer ID')
                ->disabled(),

            Select::make('status')
                ->options([
                    'waiting' => 'Waiting',
                    'active'  => 'Active',
                    'ended'   => 'Ended',
                ])
                ->required(),
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
                    ->copyable()
                    ->fontFamily(FontFamily::Mono),

                TextColumn::make('host_peer_id')
                    ->label('Host Peer ID')
                    ->limit(20)
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting' => 'warning',
                        'active'  => 'success',
                        'ended'   => 'danger',
                        default   => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->timezone('Asia/Manila') // Philippine Time!
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'active'  => 'Active',
                        'ended'   => 'Ended',
                    ]),
            ])
            // TAMA AT EXACT V4 METHODS:
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMeetings::route('/'),
            'create' => Pages\CreateMeeting::route('/create'),
            'view'   => Pages\ViewMeeting::route('/{record}'),
            'edit'   => Pages\EditMeeting::route('/{record}/edit'),
        ];
    }
}