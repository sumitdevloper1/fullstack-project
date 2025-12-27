<?php

namespace App\Http\Controllers;
use App\Models\StandardCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStandardCodeRequest;
use App\Http\Requests\UpdateStandardCodeRequest;
use Illuminate\Http\Request;

class StandardCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $standardCodes = StandardCode::all();
        return response()->json($standardCodes, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store( StoreStandardCodeRequest $request)
    {
        $standardCode = StandardCode::create($request->validated());
        return response()->json($standardCode, 201);

    }

    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStandardCodeRequest $request, string $id)
    {
       

        $standardCode = StandardCode::findOrFail($id);
        $standardCode->update($request->validated());
        return response()->json($standardCode, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $standardCode = StandardCode::findOrFail($id);
        $standardCode->delete();
        return response()->json(null, 204);
    }
}
