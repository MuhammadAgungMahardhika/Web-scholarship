<?php

namespace App\Models\Enums;

enum ApplicationStatusEnum: int
{

    case Draft = 1;
    case RequestVerify = 2;
    case Verified = 3;
    case RevisionNeeded = 4;
    case Rejected = 5;
    case Approved = 6;

    /**
     * Menyediakan label yang mudah dibaca untuk setiap status pekerjaan.
     *
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::Draft->value => 'Draft',
            self::RequestVerify->value => 'Request Validasi',
            self::Verified->value => 'Valid',
            self::RevisionNeeded->value => 'Butuh Perbaikan',
            self::Rejected->value => 'Tidak valid',
            self::Approved->value => 'Diterima',
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
            self::Draft->value => 'warning',
            self::Verified->value => 'info',
            self::RevisionNeeded->value => 'warning',
            self::Rejected->value => 'danger',
            self::Approved->value => 'success',
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
            self::Draft->value => '#adaca9ff',
            self::RequestVerify->value => '#F59E0B',
            self::Verified->value => '#3bf648ff',
            self::RevisionNeeded->value => '#F59E0B',
            self::Rejected->value =>  '#EF4444',
            default => '#adaca9ff',
        };
    }

    /**
     * Mendapatkan status default untuk pekerjaan baru.
     *
     * @return self
     */
    public static function default(): self
    {
        return self::Draft;
    }
}
