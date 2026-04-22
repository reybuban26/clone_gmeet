<?php

namespace App\Filament\Resources\MeetingLogResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityRelationManager extends RelationManager
{
    protected static string  $relationship = 'frontendLogs';
    protected static ?string $title        = 'Activity Log';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('H:i:s · M j, Y', timezone: 'Asia/Manila')
                    ->timezone('Asia/Manila')
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state) => match (true) {
                        str_contains($state, 'error')   => 'danger',
                        str_contains($state, 'joined')  => 'success',
                        str_contains($state, 'left')    => 'warning',
                        str_contains($state, 'muted')   => 'gray',
                        str_contains($state, 'caption') => 'info',
                        default                         => 'primary',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('metadata')
                    ->label('Details')
                    ->formatStateUsing(function ($state) {
                        if (! $state) return '—';
                        // Show caption transcript text directly
                        if (isset($state['text'])) {
                            $speaker = $state['speaker'] ?? '';
                            return $speaker ? "[{$speaker}] {$state['text']}" : $state['text'];
                        }
                        return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    })
                    ->wrap()
                    ->limit(200),
            ])
            ->defaultSort('created_at', 'asc')
            ->recordActions([])
            ->toolbarActions([]);
    }
}
