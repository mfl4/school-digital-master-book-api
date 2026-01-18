<?php

namespace App\Enums;

final class UserRole
{
    public const ADMIN = 'admin';
    public const GURU = 'guru';
    public const WALI_KELAS = 'wali_kelas';
    public const ALUMNI = 'alumni';

    public static function all(): array
    {
        return [
            self::ADMIN,
            self::GURU,
            self::WALI_KELAS,
            self::ALUMNI,
        ];
    }
}
