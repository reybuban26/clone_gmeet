<?php

namespace App\Filament\Resources\MeetingLogResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;

// FILAMENT V4 FIX: Inilipat na ang imports sa Filament\Actions namespace
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

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

                TextColumn::make('file_path')
                    ->label('Audio')
                    ->html()
                    ->formatStateUsing(fn ($record) => 
                        '<audio controls preload="auto" src="' . route('audio.serve', ['path' => $record->file_path]) . '" style="height: 40px; max-width: 250px;"></audio>'
                    ),

            ])
            ->defaultSort('recorded_at', 'asc')
            
            // FILAMENT V4 FIX: Pinalitan ng recordActions() ang actions()
            ->recordActions([
                DeleteAction::make(),
            ])
            
            // FILAMENT V4 FIX: Pinalitan ng toolbarActions() ang bulkActions()
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}