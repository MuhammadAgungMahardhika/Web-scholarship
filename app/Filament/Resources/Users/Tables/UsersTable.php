<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Enums\RoleEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles')
                    ->label('Hak akses')
                    ->formatStateUsing(function ($record) {
                        // Ambil semua nama role dari record user
                        $roleNames = $record->roles->pluck('name');

                        // Buat array kosong untuk menyimpan label role yang ditemukan
                        $displayLabels = [];

                        // Iterasi setiap nama role
                        foreach ($roleNames as $roleName) {
                            // Coba konversi nama role (string) ke enum case
                            $enumCase = RoleEnum::tryFrom($roleName);

                            // Jika enum case ditemukan, ambil labelnya
                            if ($enumCase) {
                                $displayLabels[] = $enumCase->label();
                            } else {
                                // Jika nama role tidak ditemukan di enum, tampilkan nama aslinya atau default
                                $displayLabels[] = ucfirst($roleName); // Mengkapitalisasi nama role jika tidak ada di enum
                            }
                        }

                        // Gabungkan semua label yang ditemukan dengan koma
                        if (empty($displayLabels)) {
                            return 'Tidak ada'; // Jika tidak ada role sama sekali
                        }

                        return implode(', ', $displayLabels);
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
