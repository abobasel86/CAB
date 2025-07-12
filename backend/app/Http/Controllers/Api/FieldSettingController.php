<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FieldSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FieldSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fieldSettings = FieldSetting::orderBy('display_order')->get();
        return response()->json($fieldSettings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $validatedData = $request->validate([
            'field_name' => 'required|string|unique:field_settings,field_name',
            'field_type' => 'required|in:imported,manual,calculated',
            'is_editable' => 'boolean',
            'display_order' => 'integer'
        ]);

        $fieldSetting = FieldSetting::create($validatedData);
        
        return response()->json($fieldSetting, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(FieldSetting $fieldSetting)
    {
        return response()->json($fieldSetting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FieldSetting $fieldSetting)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $validatedData = $request->validate([
            'field_name' => 'required|string|unique:field_settings,field_name,' . $fieldSetting->id,
            'field_type' => 'required|in:imported,manual,calculated',
            'is_editable' => 'boolean',
            'display_order' => 'integer'
        ]);

        $fieldSetting->update($validatedData);
        
        return response()->json($fieldSetting);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FieldSetting $fieldSetting)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $fieldSetting->delete();
        
        return response()->json(['message' => 'Field setting deleted successfully']);
    }

    /**
     * Get field configuration for frontend
     */
    public function getFieldConfig()
    {
        $fieldSettings = FieldSetting::orderBy('display_order')->get();
        
        $config = [
            'imported' => [],
            'manual' => [],
            'calculated' => []
        ];

        foreach ($fieldSettings as $setting) {
            $config[$setting->field_type][] = [
                'name' => $setting->field_name,
                'editable' => $setting->is_editable,
                'order' => $setting->display_order
            ];
        }

        return response()->json($config);
    }
}
