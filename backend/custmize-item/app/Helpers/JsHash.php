<?php
namespace App\Helpers;

use App\Models\Customization;

class JsHash
{
    /**
     * Emulate JavaScript simple hash function (32-bit int behavior)
     */
    public static function simpleHash(string $str): string
    {
        $hash = 0;
        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $chr = ord($str[$i]);
            $hash = (($hash << 5) - $hash + $chr) & 0xFFFFFFFF;
        }

        // Convert to signed 32-bit integer
        if ($hash & 0x80000000) {
            $hash = -((~$hash & 0xFFFFFFFF) + 1);
        }

        return (string) $hash;
    }

    /**
     * Generate unique code from request data
     */
    public static function makeUniqueFromRequestData(array $requestData): string
    {
        $parts = [
            $requestData['standard_code_id'] ?? '',
            self::getValueFromJson($requestData['printing_color_mark_json'] ?? '', 'shape'),
            self::getValueFromJson($requestData['printing_color_mark_json'] ?? '', 'location'),
            self::getValueFromJson($requestData['printing_color_mark_json'] ?? '', 'color'),
            self::getValueFromJson($requestData['printing_color_print_json'] ?? '', 'customText'),
            self::getValueFromJson($requestData['printing_color_print_json'] ?? '', 'printLocation'),
            self::getValueFromJson($requestData['printing_color_print_json'] ?? '', 'printColor'),
            !empty($requestData['engraving']) ? 'ENGRAVE' : 'NOENGRAVE',
            $requestData['engraving'] ?? '',
            $requestData['specifications'][0]['note'] ?? ($requestData['label_note'] ?? ''),
            '', // file name placeholder
            $requestData['add_accessories_data'] ?? '',
            $requestData['remove_accessories_data'] ?? ''
        ];

        $codeString = implode('|', $parts);
        $hash = self::simpleHash($codeString);

        // Ensure unique
        $base = $hash;
        $i = 0;
        while (Customization::where('unique_code', $hash)->exists()) {
            $i++;
            $hash = $base . '-' . $i;
        }

        return $hash;
    }

    /**
     * Extract value from JSON string safely
     */
    private static function getValueFromJson(string $json, string $key): string
    {
        if (empty($json)) return '';
        $arr = json_decode($json, true);
        return is_array($arr) && isset($arr[$key]) ? (string) $arr[$key] : '';
    }
}
