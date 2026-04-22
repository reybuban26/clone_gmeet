<?php

namespace App\Filament\Resources\MeetingLogResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatsRelationManager extends RelationManager
{
    protected static string  $relationship = 'chats';
    protected static ?string $title        = 'Chat Conversation';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sent_at')
                    ->label('Time')
                    ->dateTime('H:i:s · M j, Y')
                    ->sortable(),

                TextColumn::make('sender')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Host'  => 'warning',
                        'Guest' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('message')
                    ->label('Message')
                    ->wrap()
                    ->searchable(),
            ])
            ->defaultSort('sent_at', 'asc')
            ->paginated([25, 50, 100])
            ->recordActions([])
            ->bulkActions([]);
    }
}
