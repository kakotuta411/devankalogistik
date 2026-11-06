<?php
namespace App\Services;

class SealGenerator
{
    public static function generate(string $manifestNo): string
    {
        return "SEAL-{$manifestNo}";
    }
}
