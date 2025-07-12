<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldSetting extends Model
{
    protected $fillable = [
        'field_name',
        'field_type',
        'is_editable',
        'display_order'
    ];

    protected $casts = [
        'is_editable' => 'boolean',
        'display_order' => 'integer'
    ];

    public const FIELD_TYPES = [
        'imported' => 'imported',
        'manual' => 'manual',
        'calculated' => 'calculated'
    ];

    public static function getImportedFields(): array
    {
        return self::where('field_type', 'imported')->pluck('field_name')->toArray();
    }

    public static function getManualFields(): array
    {
        return self::where('field_type', 'manual')->pluck('field_name')->toArray();
    }

    public static function getCalculatedFields(): array
    {
        return self::where('field_type', 'calculated')->pluck('field_name')->toArray();
    }
}
