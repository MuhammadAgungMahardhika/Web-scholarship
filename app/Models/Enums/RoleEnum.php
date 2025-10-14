<?php
// app/Enums/PaymentStatusEnum.php

namespace App\Models\Enums;

enum RoleEnum: string
{
    case Admin = 'admin';
    case Student = 'student';



    public function id(): int
    {
        return match ($this) {
            self::Admin => 1,
            self::Student => 2,
        };
    }

    /**
     * Menyediakan label untuk setiap status peran
     *
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::Admin->value => 'Admin',
            self::Student->value => 'Student',
        ];
    }

    /**
     * Mendapatkan label dari status pembayaran
     *
     * @return string
     */
    public function label(): string
    {
        return self::labels()[$this->value];
    }


    /**
     * Mengembalikan daftar semua role dalam format array untuk dropdown, dll.
     * Key adalah value dari enum, Value adalah labelnya.
     *
     * @return array<string, string>
     */
    public static function toAssociativeArray(): array
    {
        $roles = [];
        foreach (self::cases() as $case) {
            $roles[$case->value] = $case->label();
        }
        return $roles;
    }

    /**
     * Mendapatkan default case untuk enum
     *
     * @return self
     */
    public static function default(): self
    {
        return self::Student; // Status default adalah "Pending"
    }

    /**
     * Menentukan apakah sebuah peran adalah peran inti sistem yang tidak boleh diubah/dihapus.
     * Logika ini digunakan oleh RolePolicy.
     *
     * @param string $roleId
     * @return bool
     */

    public static function isCoreSystemRole(int $roleId): bool
    {
        return in_array($roleId, [
            self::Admin->id(),
            self::Student->id(),
        ]);
    }
}
