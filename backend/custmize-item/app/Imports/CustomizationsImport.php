<?php

namespace App\Imports;

use App\Models\Customization;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Helpers\JsHash;

class CustomizationsImport implements ToModel, WithHeadingRow
{
    /**
     * Map each row (associative by heading) to a Customization model.
     * Heading keys must match the Excel header row.
     */
    public function model(array $row)
    {
        // Normalize values (trim)
        $standardCode = isset($row['standard_code']) ? trim((string)$row['standard_code']) : null;
        $printing_shape = trim((string)($row['printing_shape'] ?? ''));
        $printing_location = trim((string)($row['printing_location'] ?? ''));
        $printing_color = trim((string)($row['printing_color'] ?? ''));
        $printing_custom_text = trim((string)($row['printing_custom_text'] ?? ''));
        $printing_print_location = trim((string)($row['printing_print_location'] ?? ''));
        $printing_print_color = trim((string)($row['printing_print_color'] ?? ''));
        $engraving_enable = $this->toBoolString($row['engraving_enable'] ?? '');
        $engraving_text = trim((string)($row['engraving_text'] ?? ''));
        $label_note = trim((string)($row['label_note'] ?? ''));
        $neck_size = trim((string)($row['neck_size'] ?? ''));
        $capacity = trim((string)($row['capacity'] ?? ''));
        $accessories_add = trim((string)($row['accessories_add'] ?? ''));
        $accessories_remove = trim((string)($row['accessories_remove'] ?? ''));

        // Compose the same string parts as the Angular generateCustomizationCode()
        // Use the same order and separator "|"
        $parts = [
            $standardCode,
            $printing_shape,
            $printing_location,
            $printing_color,
            $printing_custom_text,
            $printing_print_location,
            $printing_print_color,
            $engraving_enable ? 'ENGRAVE' : 'NOENGRAVE',
            $engraving_text,
            $label_note,
            '', // file name for labelSpec.file — Excel can't include files, so empty
            $accessories_add,
            $accessories_remove,
        ];

        $codeString = implode('|', $parts);

        // Generate unique_code using the JS-style hash
        $uniqueCode = JsHash::simpleHash($codeString);

        // Ensure uniqueness in DB (in rare collision case, append digits)
        $uniqueCode = $this->ensureUnique($uniqueCode);

        // Save printing data as JSON strings similar to your Angular payload
        $printingColorMarkJson = json_encode([
            'shape' => $printing_shape,
            'location' => $printing_location,
            'color' => $printing_color,
        ]);

        $printingColorPrintJson = json_encode([
            'customText' => $printing_custom_text,
            'printLocation' => $printing_print_location,
            'printColor' => $printing_print_color,
        ]);

        return new Customization([
            'standard_code_id' => $standardCode ? intval($standardCode) : null,
            'printing_color_mark_json' => $printingColorMarkJson,
            'printing_color_print_json' => $printingColorPrintJson,
            'engraving' => $engraving_text ?: null,
            'is_specification' => 0,
            'add_accessories_data' => $accessories_add ?: null,
            'remove_accessories_data' => $accessories_remove ?: null,
            'unique_code' => $uniqueCode,
        ]);
    }

    private function toBoolString($val)
    {
        $v = strtolower(trim((string)$val));
        return in_array($v, ['1', 'true', 'yes', 'y', 'on']);
    }

    private function ensureUnique($code)
    {
        $base = $code;
        $i = 0;
        while (\App\Models\Customization::where('unique_code', $code)->exists()) {
            $i++;
            $code = $base . '-' . $i;
        }
        return $code;
    }
}
