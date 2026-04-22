<?php

namespace App\Filament\Resources\MeetingLogResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecordingsRelationManager extends RelationManager
{
    protected static string  $relationship = 'recordings';
    protected static ?string $title        = 'Voice Recordings';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Recorded At')
                    ->dateTime('H:i:s · M j, Y', timezone: 'Asia/Manila')
                    ->sortable(),

                TextColumn::make('speaker')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Host'  => 'warning',
                        'Guest' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('file_size_label')
                    ->label('File Size')
                    ->getStateUsing(fn ($record) => $record->file_size_label),

                // Clickable play/download link
                TextColumn::make('file_url')
                    ->label('Audio')
                    ->getStateUsing(fn ($record) => '▶ Play / Download')
                    ->url(fn ($record) => $record->file_url)
                    ->openUrlInNewTab()
                    ->color('info'),
            ])
            ->defaultSort('recorded_at', 'asc')
            ->recordActions([
                Action::make('play')
                    ->label('Play')
                    ->icon('heroicon-o-play')
                    ->url(fn ($record) => $record->file_url)
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->label('Delete'),
            ])
            ->bulkActions([]);
    }
}
