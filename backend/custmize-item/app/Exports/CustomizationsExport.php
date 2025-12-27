<?php

namespace App\Exports;

use App\Models\Customization;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomizationsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Customization::with('accessories')->get()->map(function ($item) {
            $accessoryNames = $item->accessories ? 
                $item->accessories->pluck('name')->implode(', ') : '';

            return [
                'Standard Code'      => $item->standard_code ?? '',
                'Product Name'       => $item->product_name ?? '',

                // Printing section
                'Printing Shape'     => $item->printing['shape'] ?? '',
                'Printing Location'  => $item->printing['location'] ?? '',
                'Printing Color'     => $item->printing['color'] ?? '',
                'Printing Text'      => $item->printing['customText'] ?? '',

                // Mark section
                'Mark Location'      => $item->printing['printLocation'] ?? '',
                'Mark Color'         => $item->printing['printColor'] ?? '',

                // Engrave
                'Engraving Enabled'  => ($item->engraving['enable'] ?? false) ? 'Yes' : 'No',
                'Engraving Text'     => $item->engraving['text'] ?? '',

                // Label Specs
                'Label Note'         => $item->label_spec['note'] ?? '',
                'Neck Size'          => $item->label_spec['neckSize'] ?? '',
                'Capacity'           => $item->label_spec['capacity'] ?? '',

                // Accessories
                'Accessories (Add)'  => $item->accessories_add ?? '',
                'Accessories (Remove)'=> $item->accessories_remove ?? '',
                'Accessories Names'  => $accessoryNames,

                'Created Date'       => $item->created_at?->format('d-M-Y'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Standard Code',
            'Product Name',
            'Printing Shape',
            'Printing Location',
            'Printing Color',
            'Printing Text',
            'Mark Location',
            'Mark Color',
            'Engraving Enabled',
            'Engraving Text',
            'Label Note',
            'Neck Size',
            'Capacity',
            'Accessories (Add)',
            'Accessories (Remove)',
            'Accessories Names',
            'Created Date'
        ];
    }
}
