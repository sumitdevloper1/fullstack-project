<?php

namespace App\Imports;

use App\Models\Customization;
use App\Models\Specification;
use App\Models\StandardCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomizationImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Get or create standard code
            $standardCode = StandardCode::firstOrCreate(
                ['code' => $row['standard_code']],
                ['status' => 'active']
            );

            // Build JSON fields
            $printing_color_print_json = json_encode([
                'customText' => $row['printing_shape'] ?? null,
                'printLocation' => $row['printing_location'] ?? null,
                'printColor' => $row['printing_color'] ?? null,

            ]);

            $printing_color_mark_json = json_encode([
                'shape' => $row['printing_text'] ?? null,
                'location' => $row['mark_location'] ?? null,
                'color' => $row['mark_color'] ?? null,
            ]);

            $add_accessories = [];
            if (!empty($row['added_accessories'])) {
                foreach (explode(',', $row['added_accessories']) as $name) {
                    $add_accessories[] = ['id' => null, 'name' => trim($name)];
                }
            }

            $remove_accessories = [];
            if (!empty($row['removed_accessories'])) {
                foreach (explode(',', $row['removed_accessories']) as $name) {
                    $remove_accessories[] = ['id' => null, 'name' => trim($name)];
                }
            }

            // Define data for insertion
            $customdata = [
                'printing_color_mark_json' => $printing_color_mark_json,
                'printing_color_print_json' => $printing_color_print_json,
                'engraving' => $row['engraving_text'] ?? null,
                'is_specification' => 'yes',
                'add_accessories_data' => json_encode($add_accessories),
                'remove_accessories_data' => json_encode($remove_accessories),
                'unique_code' => uniqid(),
                'standard_code_id' => $standardCode->id,
            ];

            // ✅ Check for duplicates without using 'unique_code'
            $exists = Customization::where('standard_code_id', $standardCode->id)
                ->where('printing_color_mark_json', $printing_color_mark_json)
                ->where('printing_color_print_json', $printing_color_print_json)
                ->where('engraving', $row['engraving_text'] ?? null)
                ->where('add_accessories_data', json_encode($add_accessories))
                ->where('remove_accessories_data', json_encode($remove_accessories))

                ->exists();

            if (!$exists) {
                // Insert customization
                $customization = Customization::create($customdata);

                // Create Specification record
                if (!empty($row['specfication_note']) || !empty($row['capacity']) || !empty($row['neck_size'])) {
                    Specification::create([
                        'file' => null,
                        'note' => $row['specfication_note'] ?? null,
                        'capacity' => $row['capacity'] ?? null,
                        'neck_size' => $row['neck_size'] ?? null,
                        // NEW FIELDS
                        'item_name'          => $row['item_name'] ?? null,
                        'item_description'   => $row['item_description'] ?? null,
                        'remarks'            => $row['remarks'] ?? null,
                        'vendor_name'        => $row['vendor_name'] ?? null,
                        'pack_size'          => $row['pack_size'] ?? null,
                        'moq'                => $row['moq'] ?? null,
                        'customization_id' => $customization->id,
                    ]);
                }
            } else {
                // Skip duplicate
                continue;
            }
        }
    }
}
