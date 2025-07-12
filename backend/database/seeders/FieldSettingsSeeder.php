<?php

namespace Database\Seeders;

use App\Models\FieldSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FieldSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [
            // Imported fields
            ['field_name' => 'post_date', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 1],
            ['field_name' => 'value_date', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 2],
            ['field_name' => 'description', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 3],
            ['field_name' => 'doctor_name', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 4],
            ['field_name' => 'reference', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 5],
            ['field_name' => 'amount', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 6],
            ['field_name' => 'balance', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 7],
            ['field_name' => 'specialist', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 8],

            // Manual fields
            ['field_name' => 'registration', 'field_type' => 'manual', 'is_editable' => true, 'display_order' => 9],
            ['field_name' => 'yearly', 'field_type' => 'manual', 'is_editable' => true, 'display_order' => 10],
            ['field_name' => 'exam', 'field_type' => 'manual', 'is_editable' => true, 'display_order' => 11],
            ['field_name' => 'certificate', 'field_type' => 'manual', 'is_editable' => true, 'display_order' => 12],
            ['field_name' => 'newsletters', 'field_type' => 'manual', 'is_editable' => true, 'display_order' => 13],
            ['field_name' => 'other', 'field_type' => 'manual', 'is_editable' => true, 'display_order' => 14],
            ['field_name' => 'visa', 'field_type' => 'manual', 'is_editable' => true, 'display_order' => 15],

            // Calculated fields
            ['field_name' => 'unspecified', 'field_type' => 'calculated', 'is_editable' => false, 'display_order' => 16],
            ['field_name' => 'summary', 'field_type' => 'calculated', 'is_editable' => false, 'display_order' => 17],
            ['field_name' => 'commission', 'field_type' => 'calculated', 'is_editable' => false, 'display_order' => 18],
            ['field_name' => 'total', 'field_type' => 'calculated', 'is_editable' => false, 'display_order' => 19],
            ['field_name' => 'difference', 'field_type' => 'calculated', 'is_editable' => false, 'display_order' => 20],

            // Additional fields
            ['field_name' => 'inward_number', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 21],
            ['field_name' => 'inward_date', 'field_type' => 'imported', 'is_editable' => false, 'display_order' => 22],
            ['field_name' => 'notes', 'field_type' => 'manual', 'is_editable' => true, 'display_order' => 23],
        ];

        foreach ($fields as $field) {
            FieldSetting::updateOrCreate(
                ['field_name' => $field['field_name']],
                $field
            );
        }
    }
}
