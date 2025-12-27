<?php

namespace App\Helpers;

use App\Models\Customization;
use Illuminate\Support\Str;
use Carbon\Carbon;
class CustomizationCodeHelper
{
    /**
     * Generate UNIQUE customization code
     * Example: 50.303.0025-A9F3
     */
    // public static function generate(string $standardCode): string
    // {
    //     do {
    //         $code = $standardCode . '-' . strtoupper(Str::random(4));
    //     } while (Customization::where('unique_code', $code)->exists());

    //     return $code;
    // }
    public static function generate(string $standardCode): string
{
    do {
        $date = Carbon::now()->format('my'); // 1225
        $random = strtoupper(Str::random(4));

        $code = $standardCode . '-' . $date . '-' . $random;

    } while (Customization::where('unique_code', $code)->exists());

    return $code;
}
}
