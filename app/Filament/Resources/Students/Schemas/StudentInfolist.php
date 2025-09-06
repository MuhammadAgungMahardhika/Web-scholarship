<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('student_number'),
                TextEntry::make('fullname'),
                TextEntry::make('study_program'),
                TextEntry::make('faculty'),
                TextEntry::make('phone_number'),
                TextEntry::make('date_of_birth')
                    ->date(),
                TextEntry::make('gpa')
                    ->numeric(),
                TextEntry::make('parent_income')
                    ->numeric(),
                TextEntry::make('created_by'),
                TextEntry::make('updated_by'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
