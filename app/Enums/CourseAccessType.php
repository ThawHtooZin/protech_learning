<?php

namespace App\Enums;

enum CourseAccessType: string
{
    case Public = 'public';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Private => 'Private',
        };
    }
}
