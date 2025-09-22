<?php

namespace App\Models\Enums;

use Filament\Support\Colors\Color;

enum DocumentStatusEnum: int
{

    case Pending = 1;
    case Verified = 2;
    case Rejected = 3;
    case Revision = 4;


    /**
     * Menyediakan label yang mudah dibaca untuk setiap status pekerjaan.
     *
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::Pending->value => 'Menunggu',
            self::Verified->value => 'Valid',
            self::Rejected->value => 'Tidak valid',
            self::Revision->value => 'Butuh perbaikan',
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
     * @param int $status
     * @return string
     */
    public static function color(int $status): string
    {
        return match ($status) {
            self::Pending->value => 'gray',
            self::Verified->value => 'success',
            self::Rejected->value => 'danger',
            self::Revision->value => 'warning',
            default => 'warning',
        };
    }

    /**
     * Menyediakan kode warna CSS (heksadesimal) untuk styling kustom.
     *
     * @param int $status
     * @return string
     */
    public static function cssColor(int $status): string
    {
        return match ($status) {
            self::Pending->value => '#d6d6d6ff',
            self::Verified->value => '#3bf648ff',
            self::Rejected->value =>  '#EF4444',
            self::Revision->value =>  '#F59E0B',
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
        return self::Pending;
    }
}
