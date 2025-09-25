<?php

namespace App\Models\Enums;

use Filament\Support\Colors\Color;

enum CriteriaDataTypeEnum: string
{

    case Number = 'number';
    case Text = 'text';
    case Select = 'select';
    case File = 'file';


    /**
     * Menyediakan label yang mudah dibaca untuk setiap status pekerjaan.
     *
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::Number->value => 'Angka',
            self::Text->value => 'Text',
            self::Select->value => 'Pilihan',
            self::File->value => 'File',
        ];
    }

    /**
     * Gets the human-readable label for the current enum case.
     *
     * @return string
     */
    public function label(): string
    {
        return self::labels()[$this->value];
    }
    /**
     * Menyediakan warna visual untuk status pekerjaan (untuk Filament).
     *
     * @param string $status
     * @return string
     */
    public static function color(string $status): string
    {
        return match ($status) {
            self::Number->value => 'gray',
            self::Text->value => 'success',
            self::Select->value => 'danger',
            self::File->value => 'warning',
            default => 'warning',
        };
    }

    /**
     * Menyediakan kode warna CSS (heksadesimal) untuk styling kustom.
     *
     * @param string $status
     * @return string
     */
    public static function cssColor(string $status): string
    {
        return match ($status) {
            self::Number->value => '#d6d6d6ff',
            self::Text->value => '#3bf648ff',
            self::Select->value =>  '#EF4444',
            self::File->value =>  '#F59E0B',
            default => '#d6d6d6ff',
        };
    }

    /**
     * Mendapatkan status default untuk pekerjaan baru.
     *
     * @return self
     */
    public static function default(): self
    {
        return self::Number;
    }
}
