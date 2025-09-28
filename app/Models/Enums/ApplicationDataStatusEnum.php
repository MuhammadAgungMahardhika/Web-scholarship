<?php

namespace App\Models\Enums;

use Filament\Support\Colors\Color;

enum ApplicationDataStatusEnum: int
{

    case Pending = 1;
    case Verified = 2;
    case Revision = 3;
    case Rejected = 4;


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
            self::Revision->value => 'Butuh perbaikan',
            self::Rejected->value => 'Tidak valid',
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
            self::Pending->value => 'warning',
            self::Verified->value => 'success',
            self::Revision->value => 'warning',
            self::Rejected->value => 'danger',
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
            self::Pending->value => '#F59E0B',
            self::Verified->value => '#3bf648ff',
            self::Revision->value =>  '#F59E0B',
            self::Rejected->value =>  '#EF4444',
            default => '#F59E0B',
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
