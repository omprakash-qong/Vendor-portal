<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryFieldsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('category_fields')->truncate();

        $fields = [
            // ── Motors ──────────────────────────────────────────────────
            ['category_slug' => 'motors', 'field_name' => 'power_kw',          'field_label' => 'Power (kW)',           'field_type' => 'range',  'unit' => 'kW',   'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 1],
            ['category_slug' => 'motors', 'field_name' => 'poles',             'field_label' => 'Poles',                'field_type' => 'select', 'unit' => null,   'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 2, 'options_json' => json_encode(['2','4','6','8'])],
            ['category_slug' => 'motors', 'field_name' => 'voltage_v',         'field_label' => 'Voltage (V)',          'field_type' => 'select', 'unit' => 'V',    'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 3, 'options_json' => json_encode(['220','380','415','440','690'])],
            ['category_slug' => 'motors', 'field_name' => 'frame_size',        'field_label' => 'Frame Size',           'field_type' => 'text',   'unit' => null,   'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 4],
            ['category_slug' => 'motors', 'field_name' => 'efficiency_class',  'field_label' => 'Efficiency Class',     'field_type' => 'select', 'unit' => null,   'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 5, 'options_json' => json_encode(['IE2','IE3','IE4','IE5'])],
            ['category_slug' => 'motors', 'field_name' => 'rpm',               'field_label' => 'Speed (RPM)',          'field_type' => 'range',  'unit' => 'RPM',  'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 6],
            ['category_slug' => 'motors', 'field_name' => 'ip_rating',         'field_label' => 'IP Rating',            'field_type' => 'text',   'unit' => null,   'is_matching_field' => false, 'is_filter' => false, 'sort_order' => 7],

            // ── Valves ───────────────────────────────────────────────────
            ['category_slug' => 'valves', 'field_name' => 'size_mm',           'field_label' => 'DN Size (mm)',         'field_type' => 'range',  'unit' => 'mm',   'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 1],
            ['category_slug' => 'valves', 'field_name' => 'size_inch',         'field_label' => 'Size (inch)',          'field_type' => 'range',  'unit' => '"',    'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 2],
            ['category_slug' => 'valves', 'field_name' => 'pressure_bar',      'field_label' => 'Pressure Rating (bar)','field_type' => 'range',  'unit' => 'bar',  'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 3],
            ['category_slug' => 'valves', 'field_name' => 'material',          'field_label' => 'Body Material',        'field_type' => 'select', 'unit' => null,   'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 4, 'options_json' => json_encode(['Cast Iron','Ductile Iron','Stainless Steel','Carbon Steel','Bronze','PVC'])],
            ['category_slug' => 'valves', 'field_name' => 'connection_type',   'field_label' => 'Connection Type',      'field_type' => 'select', 'unit' => null,   'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 5, 'options_json' => json_encode(['Flanged','Threaded','Butt Weld','Socket Weld','Wafer'])],
            ['category_slug' => 'valves', 'field_name' => 'actuation_type',    'field_label' => 'Actuation Type',       'field_type' => 'select', 'unit' => null,   'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 6, 'options_json' => json_encode(['Manual','Electric','Pneumatic','Hydraulic'])],
            ['category_slug' => 'valves', 'field_name' => 'valve_type',        'field_label' => 'Valve Type',           'field_type' => 'select', 'unit' => null,   'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 7, 'options_json' => json_encode(['Gate','Globe','Ball','Butterfly','Check','Needle','Plug'])],

            // ── Pumps ────────────────────────────────────────────────────
            ['category_slug' => 'pumps',  'field_name' => 'flow_m3h',          'field_label' => 'Flow Rate (m³/h)',     'field_type' => 'range',  'unit' => 'm³/h', 'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 1],
            ['category_slug' => 'pumps',  'field_name' => 'head_m',            'field_label' => 'Head (m)',             'field_type' => 'range',  'unit' => 'm',    'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 2],
            ['category_slug' => 'pumps',  'field_name' => 'power_kw',          'field_label' => 'Power (kW)',           'field_type' => 'range',  'unit' => 'kW',   'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 3],
            ['category_slug' => 'pumps',  'field_name' => 'pressure_bar',      'field_label' => 'Max Pressure (bar)',   'field_type' => 'range',  'unit' => 'bar',  'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 4],
            ['category_slug' => 'pumps',  'field_name' => 'pump_type',         'field_label' => 'Pump Type',            'field_type' => 'select', 'unit' => null,   'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 5, 'options_json' => json_encode(['Centrifugal','Submersible','Gear','Piston','Diaphragm','Peristaltic'])],
            ['category_slug' => 'pumps',  'field_name' => 'fluid_type',        'field_label' => 'Fluid Type',           'field_type' => 'text',   'unit' => null,   'is_matching_field' => false, 'is_filter' => false, 'sort_order' => 6],

            // ── Compressors ──────────────────────────────────────────────
            ['category_slug' => 'compressors', 'field_name' => 'power_kw',       'field_label' => 'Power (kW)',         'field_type' => 'range',  'unit' => 'kW',    'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 1],
            ['category_slug' => 'compressors', 'field_name' => 'pressure_bar',   'field_label' => 'Max Pressure (bar)', 'field_type' => 'range',  'unit' => 'bar',   'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 2],
            ['category_slug' => 'compressors', 'field_name' => 'fad_m3min',      'field_label' => 'FAD (m³/min)',       'field_type' => 'range',  'unit' => 'm³/min','is_matching_field' => true,  'is_filter' => true,  'sort_order' => 3],
            ['category_slug' => 'compressors', 'field_name' => 'compressor_type','field_label' => 'Compressor Type',    'field_type' => 'select', 'unit' => null,    'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 4, 'options_json' => json_encode(['Screw','Piston','Centrifugal','Axial','Scroll'])],

            // ── Instruments ───────────────────────────────────────────────
            ['category_slug' => 'instruments', 'field_name' => 'measurement_range', 'field_label' => 'Measurement Range', 'field_type' => 'text', 'unit' => null, 'is_matching_field' => true,  'is_filter' => false, 'sort_order' => 1],
            ['category_slug' => 'instruments', 'field_name' => 'accuracy',          'field_label' => 'Accuracy',          'field_type' => 'text', 'unit' => '%',  'is_matching_field' => false, 'is_filter' => false, 'sort_order' => 2],
            ['category_slug' => 'instruments', 'field_name' => 'output_signal',     'field_label' => 'Output Signal',     'field_type' => 'select', 'unit' => null, 'is_matching_field' => false, 'is_filter' => true, 'sort_order' => 3, 'options_json' => json_encode(['4-20mA','0-10V','HART','Modbus','Profibus'])],
            ['category_slug' => 'instruments', 'field_name' => 'process_connection','field_label' => 'Process Connection','field_type' => 'text', 'unit' => null, 'is_matching_field' => false, 'is_filter' => false, 'sort_order' => 4],

            // ── Gearboxes ─────────────────────────────────────────────────
            ['category_slug' => 'gearboxes', 'field_name' => 'ratio',           'field_label' => 'Gear Ratio',          'field_type' => 'text',   'unit' => null,  'is_matching_field' => true,  'is_filter' => false, 'sort_order' => 1],
            ['category_slug' => 'gearboxes', 'field_name' => 'power_kw',        'field_label' => 'Input Power (kW)',    'field_type' => 'range',  'unit' => 'kW',  'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 2],
            ['category_slug' => 'gearboxes', 'field_name' => 'output_torque',   'field_label' => 'Output Torque (Nm)', 'field_type' => 'range',  'unit' => 'Nm',  'is_matching_field' => false, 'is_filter' => false, 'sort_order' => 3],

            // ── Actuators ─────────────────────────────────────────────────
            ['category_slug' => 'actuators', 'field_name' => 'actuator_type',   'field_label' => 'Actuator Type',       'field_type' => 'select', 'unit' => null,  'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 1, 'options_json' => json_encode(['Electric','Pneumatic','Hydraulic'])],
            ['category_slug' => 'actuators', 'field_name' => 'torque_nm',       'field_label' => 'Torque (Nm)',         'field_type' => 'range',  'unit' => 'Nm',  'is_matching_field' => true,  'is_filter' => true,  'sort_order' => 2],
            ['category_slug' => 'actuators', 'field_name' => 'voltage_v',       'field_label' => 'Voltage (V)',         'field_type' => 'select', 'unit' => 'V',   'is_matching_field' => false, 'is_filter' => true,  'sort_order' => 3, 'options_json' => json_encode(['24','110','220','380'])],
        ];

        foreach ($fields as $field) {
            DB::table('category_fields')->insert(array_merge([
                'is_required'       => false,
                'options_json'      => null,
                'unit'              => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ], $field));
        }
    }
}
