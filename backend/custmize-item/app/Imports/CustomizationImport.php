<?php

namespace App\Imports;

use App\Models\Customization;
use App\Models\Specification;
use App\Models\StandardCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Helpers\CustomizationCodeHelper;

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

           
             $printing_color_print_json = json_encode([
                'customText' => $row['printing_text'] ?? null,
                'printLocation' => $row['printing_location'] ?? null,
                'printColor' => $row['printing_color'] ?? null,

            ]);

            $printing_color_mark_json = json_encode([
                'shape' => $row['mark_shape'] ?? null,
                'location' => $row['mark_location'] ?? null,
                'color' => $row['mark_color'] ?? null,
            ]);

            // Accessories
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

            // Customization data
            $customdata = [
                'printing_color_mark_json'  => $printing_color_mark_json,
                'printing_color_print_json' => $printing_color_print_json,
                'engraving'                 => $row['engraving_text'] ?? null,
                'is_specification'          => 'yes',
                'add_accessories_data'      => json_encode($add_accessories),
                'remove_accessories_data'   => json_encode($remove_accessories),
                // ✅ BACKEND UNIQUE CODE (NO DUPLICATE)
    'unique_code'               => CustomizationCodeHelper::generate($standardCode->code),
                'standard_code_id'          => $standardCode->id,
            ];

            // Check duplicate
            $exists = Customization::where('standard_code_id', $standardCode->id)
                ->where('printing_color_mark_json', $printing_color_mark_json)
                ->where('printing_color_print_json', $printing_color_print_json)
                ->where('engraving', $row['engraving_text'] ?? null)
                ->where('add_accessories_data', json_encode($add_accessories))
                ->where('remove_accessories_data', json_encode($remove_accessories))
                ->exists();

            if ($exists) {
                continue; // skip duplicate
            }

            // Create customization
            $customization = Customization::create($customdata);

            // ALWAYS SAVE SPECIFICATION
            Specification::create([
                'file'              => null,
                'note'              => $row['specfication_note'] ?? null,
                'capacity'          => $row['capacity'] ?? null,
                'neck_size'         => $row['neck_size'] ?? null,
                'item_name'         => $row['item_name'] ?? null,
                'item_description'  => $row['item_description'] ?? null,
                'remarks'           => $row['remarks'] ?? null,
                'vendor_name'       => $row['vendor_name'] ?? null,
                'pack_size'         => $row['pack_size'] ?? null,
                'moq'               => $row['moq'] ?? null,
                'customization_id'  => $customization->id,
            ]);
        }
    }
}
