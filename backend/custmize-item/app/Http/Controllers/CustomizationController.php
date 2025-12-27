<?php

namespace App\Http\Controllers;

use App\Helpers\CustomizationCodeHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomizationRequest;
use App\Http\Requests\UpdateCustomizationRequest;
use App\Imports\CustomizationImport;
use App\Imports\CustomizationsImport;
use App\Models\Customization;
use App\Models\Specification;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StandardCode;

class CustomizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customizations = Customization::with(['specifications', 'standardCode'])->get()->map(function ($customization) {
            $customization->standard_code_display = $customization->standardCode
                ? $customization->standardCode->code . ' - ' . $customization->standardCode->name
                : null;
            return $customization;
        });

        return response()->json($customizations, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used in API-based controllers
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreCustomizationRequest $request)
    // {
    //     $requestData = $request->validated();  // Ensure data has been validated

    //     try {
    //         // Creating Customization
    //         $customization = Customization::create([
    //             'printing_color_mark_json'  => $requestData['printing_color_mark_json'] ?? null,
    //             'printing_color_print_json' => $requestData['printing_color_print_json'] ?? null,
    //             'engraving'                 => $requestData['engraving'] ?? null,
    //             'is_specification'          => $requestData['is_specification'] ?? null,
    //             'add_accessories_data'      => $requestData['add_accessories_data'] ?? null,
    //             'remove_accessories_data'   => $requestData['remove_accessories_data'] ?? null,
    //             'unique_code'               => $requestData['unique_code'] ?? null,
    //             'standard_code_id'          => $requestData['standard_code_id'] ?? null,
    //         ]);

    //         // Loop through specifications and handle file uploads
    //         foreach ($requestData['specifications'] ?? [] as $specData) {
    //             $filePath = null;

    //             // Handle file upload if present
    //             if (!empty($specData['file']) && $specData['file']->isValid()) {
    //                 // Define the upload path
    //                 $uploadPath = public_path('uploads/specifications');

    //                 // Create the folder if it doesn't exist
    //                 if (!file_exists($uploadPath)) {
    //                     mkdir($uploadPath, 0777, true);
    //                 }

    //                 // Generate a unique file name for the uploaded file
    //                 $fileName = time() . '_' . uniqid() . '.' . $specData['file']->getClientOriginalExtension();
    //                 $specData['file']->move($uploadPath, $fileName);

    //                 // Set file path after successful upload
    //                 $filePath = 'uploads/specifications/' . $fileName;
    //             }

    //             // Create the specification record, saving all fields (including neck_size, capacity, etc.)
    //             $customization->specifications()->create([
    //                 'file' => $filePath,  // File is either null or the path to the uploaded file
    //                 'note' => $specData['note'] ?? null,  // Handle note even if not provided
    //                 'capacity' => $specData['capacity'] ?? null,  // Handle capacity even if not provided
    //                 'neck_size' => $specData['neck_size'] ?? null,  // Handle neck size even if not provided
    //                 'item_name'        => $specData['item_name'] ?? null,
    //                 'item_description' => $specData['item_description'] ?? null,
    //                 'remarks'          => $specData['remarks'] ?? null,
    //                 'vendor_name'      => $specData['vendor_name'] ?? null,
    //                 'pack_size'        => $specData['pack_size'] ?? null,
    //                 'moq'              => $specData['moq'] ?? null,
    //             ]);
    //         }

    //         // Return success response with created customization data
    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Customization and specifications created successfully',
    //             'data'    => $customization->load('specifications'),
    //         ], 201);
    //     } catch (\Throwable $e) {
    //         // Return error response in case of failure
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Failed to create customization and specifications',
    //             'error'   => $e->getMessage(),  // Log specific error message
    //         ], 500);
    //     }
    // }

 public function store(StoreCustomizationRequest $request)
{
    $requestData = $request->validated();

    DB::beginTransaction();

    try {

        // ✅ Standard code string
        $standardCode = StandardCode::findOrFail($requestData['standard_code_id']);

        // 🔒 DUPLICATE DATA CHECK
        $exists = Customization::where('standard_code_id', $requestData['standard_code_id'])
            ->where('printing_color_mark_json', $requestData['printing_color_mark_json'] ?? null)
            ->where('printing_color_print_json', $requestData['printing_color_print_json'] ?? null)
            ->where('engraving', $requestData['engraving'] ?? null)
            ->where('add_accessories_data', $requestData['add_accessories_data'] ?? null)
            ->where('remove_accessories_data', $requestData['remove_accessories_data'] ?? null)
            ->exists();

        if ($exists) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Duplicate customization data not allowed',
            ], 422);
        }
        // ✅ Unique code
        $uniqueCode = CustomizationCodeHelper::generate($standardCode->code);

        $isSpec = in_array(($requestData['is_specification'] ?? 'no'), ['yes', 'no'])
            ? $requestData['is_specification']
            : 'no';

        // ✅ Create customization
        $customization = Customization::create([
            'printing_color_mark_json'  => $requestData['printing_color_mark_json'] ?? null,
            'printing_color_print_json' => $requestData['printing_color_print_json'] ?? null,
            'engraving'                 => $requestData['engraving'] ?? null,
            'is_specification'          => $isSpec,
            'add_accessories_data'      => $requestData['add_accessories_data'] ?? null,
            'remove_accessories_data'   => $requestData['remove_accessories_data'] ?? null,
            'standard_code_id'          => $requestData['standard_code_id'],
            'unique_code'               => $uniqueCode,
        ]);

        // ✅ SAVE SPECIFICATIONS (THIS WAS MISSING)
        foreach ($requestData['specifications'] ?? [] as $specData) {
            $filePath = null;

            if (!empty($specData['file']) && $specData['file']->isValid()) {
                $uploadPath = public_path('uploads/specifications');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                $fileName = time().'_'.uniqid().'.'.$specData['file']->getClientOriginalExtension();
                $specData['file']->move($uploadPath, $fileName);

                $filePath = 'uploads/specifications/'.$fileName;
            }

            $customization->specifications()->create([
                'file'              => $filePath,
                'note'              => $specData['note'] ?? null,
                'capacity'          => $specData['capacity'] ?? null,
                'neck_size'         => $specData['neck_size'] ?? null,
                'item_name'         => $specData['item_name'] ?? null,
                'item_description'  => $specData['item_description'] ?? null,
                'remarks'           => $specData['remarks'] ?? null,
                'vendor_name'       => $specData['vendor_name'] ?? null,
                'pack_size'         => $specData['pack_size'] ?? null,
                'moq'               => $specData['moq'] ?? null,
            ]);
        }

        DB::commit();

        // ✅ Decode JSON for frontend
        $customization->printing_color_mark =
            json_decode($customization->printing_color_mark_json, true);

        $customization->printing_color_print =
            json_decode($customization->printing_color_print_json, true);

        $customization->add_accessories =
            json_decode($customization->add_accessories_data, true);

        $customization->remove_accessories =
            json_decode($customization->remove_accessories_data, true);

        return response()->json([
            'status'      => true,
            'message'     => 'Customization created successfully',
            'unique_code' => $uniqueCode,
            'data'        => $customization->load('specifications'),
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => 'Failed to create customization',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customization = Customization::with(['specifications.files', 'standardCode'])->findOrFail($id);

        $customization->standard_code_display = $customization->standardCode
            ? $customization->standardCode->code . ' - ' . $customization->standardCode->name
            : null;

        return response()->json($customization, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Not used in API-based controllers
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(UpdateCustomizationRequest $request,  $id)
    {
        $requestData = $request->validated();
        $customization = Customization::findOrFail($id);
        try {
            $customization->update([
                'printing_color_mark_json'  => $requestData['printing_color_mark_json'] ?? null,
                'printing_color_print_json' => $requestData['printing_color_print_json'] ?? null,
                'engraving'                 => $requestData['engraving'] ?? null,
                'is_specification'          => $requestData['is_specification'] ?? null,
                'add_accessories_data'      => $requestData['add_accessories_data'] ?? null,
                'remove_accessories_data'   => $requestData['remove_accessories_data'] ?? null,
                // 'unique_code'               => $requestData['unique_code'] ?? null,
                'standard_code_id'          => $requestData['standard_code_id'] ?? null,

            ]);

            if (!empty($requestData['specifications'])) {
                $customization->specifications()->delete();

                foreach ($requestData['specifications'] as $specData) {
                    $filePath = null;

                    if (!empty($specData['file']) && $specData['file']->isValid()) {
                        $uploadPath = public_path('uploads/specifications');
                        if (!file_exists($uploadPath)) {
                            mkdir($uploadPath, 0777, true);
                        }

                        $fileName = time() . '_' . uniqid() . '.' . $specData['file']->getClientOriginalExtension();
                        $specData['file']->move($uploadPath, $fileName);
                        $filePath = 'uploads/specifications/' . $fileName;
                    }

                    $customization->specifications()->create([
                        'file' => $filePath,
                        'note' => $specData['note'] ?? null,
                        'capacity' => $specData['capacity'] ?? null,
                        'neck_size' => $specData['neck_size'] ?? null,
                        'item_name'        => $specData['item_name'] ?? null,
                        'item_description' => $specData['item_description'] ?? null,
                        'remarks'          => $specData['remarks'] ?? null,
                        'vendor_name'      => $specData['vendor_name'] ?? null,
                        'pack_size'        => $specData['pack_size'] ?? null,
                        'moq'              => $specData['moq'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Customization and specifications updated successfully',
                'data'    => $customization->load('specifications'),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update customization and specifications',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customization = Customization::findOrFail($id);
        $customization->delete();

        return response()->json([
            'status' => true,
            'message' => 'Customization deleted successfully',
        ], 204);
    }
     /**
     * ✅ BULK DELETE CUSTOMIZATIONS
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:customizations,id',
        ]);

        Customization::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Selected customizations deleted successfully',
        ], 200);
    }
    /**
     * ✅ IMPORT CUSTOMIZATIONS FROM EXCEL
     */
    public function import(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'Please upload an Excel file'], 400);
        }
        try {
            Excel::import(new CustomizationImport, $request->file('file'));

            return response()->json(['message' => 'Excel imported successfully']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
