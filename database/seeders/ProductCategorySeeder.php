<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Motors',
                'equipment_type' => 'Motor',
                'match_fields' => ['power_kw', 'poles', 'voltage_v'],
                'spec_template' => [
                    'fields' => [
                        ['key' => 'frame_size',       'label' => 'Frame Size',       'type' => 'text',   'example' => '132M'],
                        ['key' => 'rpm',               'label' => 'Rated RPM',        'type' => 'number'],
                        ['key' => 'voltage',           'label' => 'Voltage (V)',       'type' => 'select', 'options' => ['415','690','220']],
                        ['key' => 'ip_rating',         'label' => 'IP Rating',         'type' => 'select', 'options' => ['IP55','IP65','IP66']],
                        ['key' => 'efficiency_class',  'label' => 'IE Class',          'type' => 'select', 'options' => ['IE2','IE3','IE4']],
                        ['key' => 'mounting',          'label' => 'Mounting',          'type' => 'select', 'options' => ['B3','B5','B14','B35']],
                        ['key' => 'insulation_class',  'label' => 'Insulation Class',  'type' => 'select', 'options' => ['F','H']],
                    ],
                ],
                'children' => [
                    ['name' => 'IE4 Motors',          'equipment_type' => 'Motor'],
                    ['name' => 'IE3 Motors',          'equipment_type' => 'Motor'],
                    ['name' => 'Flame-proof Motors',  'equipment_type' => 'Motor'],
                    ['name' => 'Servo Motors',        'equipment_type' => 'Servo Motor'],
                ],
            ],
            [
                'name' => 'Valves',
                'equipment_type' => 'Valve',
                'match_fields' => ['size_inch', 'size_mm', 'pressure_bar'],
                'spec_template' => [
                    'fields' => [
                        ['key' => 'valve_type',      'label' => 'Valve Type',      'type' => 'select', 'options' => ['Gate','Globe','Ball','Butterfly','Check','Control','Angle Seat','Solenoid']],
                        ['key' => 'end_connection',  'label' => 'End Connection',  'type' => 'select', 'options' => ['Flanged','Threaded','Socket Weld','Butt Weld','Wafer']],
                        ['key' => 'body_material',   'label' => 'Body Material',   'type' => 'text'],
                        ['key' => 'trim_material',   'label' => 'Trim Material',   'type' => 'text'],
                        ['key' => 'pressure_class',  'label' => 'Pressure Class',  'type' => 'select', 'options' => ['150#','300#','600#','900#','1500#','PN10','PN16','PN25','PN40']],
                    ],
                ],
                'children' => [
                    ['name' => 'Gate Valves',           'equipment_type' => 'Gate Valve'],
                    ['name' => 'Globe Valves',          'equipment_type' => 'Globe Valve'],
                    ['name' => 'Ball Valves',           'equipment_type' => 'Ball Valve'],
                    ['name' => 'Butterfly Valves',      'equipment_type' => 'Butterfly Valve'],
                    ['name' => 'Check Valves',          'equipment_type' => 'Check Valve'],
                    ['name' => 'Control Valves',        'equipment_type' => 'Control Valve'],
                    ['name' => 'Solenoid Valves',       'equipment_type' => 'Solenoid Valve'],
                    ['name' => 'Angle Seat Valves',     'equipment_type' => 'Angle Seat Valve'],
                ],
            ],
            [
                'name' => 'Pumps',
                'equipment_type' => 'Pump',
                'match_fields' => ['power_kw', 'flow_m3h', 'pressure_bar'],
                'spec_template' => [
                    'fields' => [
                        ['key' => 'pump_type',     'label' => 'Pump Type',          'type' => 'select', 'options' => ['Centrifugal','Submersible','Gear','Diaphragm','Screw','Peristaltic']],
                        ['key' => 'head_m',        'label' => 'Head (m)',            'type' => 'number'],
                        ['key' => 'impeller_mat',  'label' => 'Impeller Material',   'type' => 'text'],
                        ['key' => 'casing_mat',    'label' => 'Casing Material',     'type' => 'text'],
                        ['key' => 'connection',    'label' => 'Inlet/Outlet (mm)',   'type' => 'text'],
                    ],
                ],
                'children' => [
                    ['name' => 'Centrifugal Pumps',  'equipment_type' => 'Centrifugal Pump'],
                    ['name' => 'Submersible Pumps',  'equipment_type' => 'Submersible Pump'],
                    ['name' => 'Gear Pumps',         'equipment_type' => 'Gear Pump'],
                    ['name' => 'Diaphragm Pumps',    'equipment_type' => 'Diaphragm Pump'],
                ],
            ],
            [
                'name' => 'Compressors',
                'equipment_type' => 'Compressor',
                'match_fields' => ['power_kw', 'pressure_bar', 'flow_m3h'],
                'spec_template' => [
                    'fields' => [
                        ['key' => 'compressor_type', 'label' => 'Type',              'type' => 'select', 'options' => ['Screw','Piston','Centrifugal','Vane']],
                        ['key' => 'fad_m3min',       'label' => 'FAD (m³/min)',       'type' => 'number'],
                        ['key' => 'stages',          'label' => 'Stages',            'type' => 'number'],
                        ['key' => 'cooling',         'label' => 'Cooling',           'type' => 'select', 'options' => ['Air','Water','Oil']],
                        ['key' => 'drive',           'label' => 'Drive',             'type' => 'select', 'options' => ['Belt','Direct','VFD']],
                    ],
                ],
                'children' => [
                    ['name' => 'Screw Compressors',       'equipment_type' => 'Screw Compressor'],
                    ['name' => 'Piston Compressors',      'equipment_type' => 'Piston Compressor'],
                    ['name' => 'Centrifugal Compressors', 'equipment_type' => 'Centrifugal Compressor'],
                ],
            ],
            [
                'name' => 'Vacuum Equipment',
                'equipment_type' => 'Vacuum Pump',
                'match_fields' => ['power_kw', 'flow_m3h', 'pressure_bar'],
                'spec_template' => [
                    'fields' => [
                        ['key' => 'vacuum_type',     'label' => 'Type',              'type' => 'select', 'options' => ['Rotary Vane','Liquid Ring','Dry Screw','Ejector']],
                        ['key' => 'ultimate_vac',    'label' => 'Ultimate Vacuum (mbar)', 'type' => 'number'],
                        ['key' => 'pumping_speed',   'label' => 'Pumping Speed (m³/h)', 'type' => 'number'],
                    ],
                ],
                'children' => [
                    ['name' => 'Rotary Vane Vacuum Pumps',  'equipment_type' => 'Rotary Vane Vacuum Pump'],
                    ['name' => 'Liquid Ring Vacuum Pumps',  'equipment_type' => 'Liquid Ring Vacuum Pump'],
                    ['name' => 'Dry Screw Vacuum Pumps',    'equipment_type' => 'Dry Screw Vacuum Pump'],
                ],
            ],
            [
                'name' => 'Blowers',
                'equipment_type' => 'Blower',
                'match_fields' => ['power_kw', 'flow_m3h', 'pressure_bar'],
                'spec_template' => [
                    'fields' => [
                        ['key' => 'blower_type',  'label' => 'Type',               'type' => 'select', 'options' => ['Side Channel','Roots','Centrifugal','Regenerative']],
                        ['key' => 'max_pressure', 'label' => 'Max Pressure (mbar)', 'type' => 'number'],
                        ['key' => 'noise_db',     'label' => 'Noise Level (dB)',    'type' => 'number'],
                    ],
                ],
                'children' => [
                    ['name' => 'Side Channel Blowers',   'equipment_type' => 'Side Channel Blower'],
                    ['name' => 'Roots Blowers',          'equipment_type' => 'Roots Blower'],
                ],
            ],
            [
                'name' => 'Pneumatics',
                'equipment_type' => 'Pneumatic',
                'match_fields' => ['size_mm', 'pressure_bar'],
                'spec_template' => [
                    'fields' => [
                        ['key' => 'cylinder_type',  'label' => 'Type',             'type' => 'select', 'options' => ['Standard','Compact','Rodless','Rotary']],
                        ['key' => 'bore_mm',        'label' => 'Bore (mm)',         'type' => 'number'],
                        ['key' => 'stroke_mm',      'label' => 'Stroke (mm)',       'type' => 'number'],
                        ['key' => 'port_size',      'label' => 'Port Size',         'type' => 'text'],
                    ],
                ],
                'children' => [
                    ['name' => 'Pneumatic Cylinders',  'equipment_type' => 'Pneumatic Cylinder'],
                    ['name' => 'Air Preparation',      'equipment_type' => 'Air Filter Regulator'],
                ],
            ],
            [
                'name' => 'Instruments',
                'equipment_type' => 'Instrument',
                'match_fields' => ['size_inch', 'pressure_bar'],
                'spec_template' => [
                    'fields' => [
                        ['key' => 'instrument_type',  'label' => 'Instrument Type',  'type' => 'select', 'options' => ['Pressure Transmitter','Flow Meter','Level Transmitter','Temperature Transmitter','Pressure Gauge','Flow Indicator']],
                        ['key' => 'range_min',        'label' => 'Range Min',         'type' => 'number'],
                        ['key' => 'range_max',        'label' => 'Range Max',         'type' => 'number'],
                        ['key' => 'output_signal',    'label' => 'Output Signal',     'type' => 'select', 'options' => ['4-20mA','HART','Modbus','Profibus','Digital']],
                        ['key' => 'process_conn',     'label' => 'Process Connection','type' => 'text'],
                    ],
                ],
                'children' => [
                    ['name' => 'Pressure Transmitters',     'equipment_type' => 'Pressure Transmitter'],
                    ['name' => 'Flow Meters',               'equipment_type' => 'Flow Meter'],
                    ['name' => 'Level Transmitters',        'equipment_type' => 'Level Transmitter'],
                    ['name' => 'Temperature Instruments',   'equipment_type' => 'Temperature Transmitter'],
                ],
            ],
        ];

        foreach ($categories as $cat) {
            $children = $cat['children'] ?? [];
            unset($cat['children']);

            $parent = ProductCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                array_merge($cat, ['level' => 1])
            );

            foreach ($children as $child) {
                ProductCategory::updateOrCreate(
                    ['slug' => Str::slug($child['name'])],
                    array_merge($child, [
                        'parent_id'      => $parent->id,
                        'level'          => 2,
                        'spec_template'  => $parent->spec_template,
                        'match_fields'   => $parent->match_fields,
                    ])
                );
            }
        }
    }
}
