<?php

/*
|--------------------------------------------------------------------------
| Category Specification Fields — single source of truth
|--------------------------------------------------------------------------
| Phase 1 predefined fields per category (Qong marketplace).
| Drives: the manual Add Product form, the Review/Edit form, and the
| scraper field-matching (App\Services\Catalogue\CategorySpecMapper).
|
| Each entry: canonical_field_key => Human Label
| Keys map 1:1 to the per-category specification columns in the spec.
*/

return [

    'Motors' => [
        'power_min_kw'      => 'Power Min (kW)',
        'power_max_kw'      => 'Power Max (kW)',
        'synchronous_rpm'   => 'Synchronous RPM',
        'mounting_type'     => 'Mounting Type',
        'frame_size'        => 'Frame Size',
        'protection_rating' => 'Protection Rating',
        'insulation_class'  => 'Insulation Class',
        'voltage'           => 'Voltage',
        'frequency'         => 'Frequency',
        'duty_type'         => 'Duty Type',
        'phase'             => 'Phase',
        'efficiency_class'  => 'Efficiency Class',
        'cooling_type'      => 'Cooling Type',
    ],

    'Pumps' => [
        'pump_type'         => 'Pump Type',
        'flow_rate_min'     => 'Flow Rate Min',
        'flow_rate_max'     => 'Flow Rate Max',
        'head_min'          => 'Head Min',
        'head_max'          => 'Head Max',
        'power_kw'          => 'Power (kW)',
        'rpm'               => 'RPM',
        'efficiency'        => 'Efficiency',
        'suction_size'      => 'Suction Size',
        'discharge_size'    => 'Discharge Size',
        'casing_material'   => 'Casing Material',
        'impeller_material' => 'Impeller Material',
        'seal_type'         => 'Seal Type',
        'temperature_range' => 'Temperature Range',
    ],

    'Valves' => [
        'certifications'         => 'Certifications',
        'critical_service'       => 'Critical Service',
        'flow_characteristics'   => 'Flow Characteristics',
        'material'               => 'Material',
        'operating_temperature'  => 'Operating Temperature',
        'pressure_class'         => 'Pressure Class',
        'process_connection_type'=> 'Process Connection Type',
        'shutoff_class'          => 'Shutoff Class',
        'valve_size'             => 'Valve Size',
        'valve_size_standard'    => 'Valve Size Standard',
    ],

    'Blowers' => [
        'blower_type'       => 'Blower Type',
        'air_flow_rate'     => 'Air Flow Rate',
        'static_pressure'   => 'Static Pressure',
        'power_kw'          => 'Power (kW)',
        'rpm'               => 'RPM',
        'efficiency'        => 'Efficiency',
        'impeller_type'     => 'Impeller Type',
        'motor_type'        => 'Motor Type',
        'voltage'           => 'Voltage',
        'frequency'         => 'Frequency',
        'noise_level'       => 'Noise Level',
        'temperature_range' => 'Temperature Range',
    ],

    'Compressors' => [
        'compressor_type'       => 'Compressor Type',
        'flow_rate'             => 'Flow Rate',
        'pressure_rating'       => 'Pressure Rating',
        'power_kw'              => 'Power (kW)',
        'rpm'                   => 'RPM',
        'voltage'               => 'Voltage',
        'frequency'             => 'Frequency',
        'cooling_method'        => 'Cooling Method',
        'lubrication_type'      => 'Lubrication Type',
        'discharge_temperature' => 'Discharge Temperature',
        'air_receiver_capacity' => 'Air Receiver Capacity',
        'efficiency'            => 'Efficiency',
    ],

    'Pressure Transmitters' => [
        'measurement_min'              => 'Measurement Min',
        'measurement_max'              => 'Measurement Max',
        'measurement_unit'             => 'Measurement Unit',
        'accuracy'                     => 'Accuracy',
        'turndown_ratio'               => 'Turndown Ratio',
        'output_signal'                => 'Output Signal',
        'communication_protocol'       => 'Communication Protocol',
        'process_connection'           => 'Process Connection',
        'wetted_material'              => 'Wetted Material',
        'diaphragm_material'           => 'Diaphragm Material',
        'power_supply'                 => 'Power Supply',
        'enclosure_rating'             => 'Enclosure Rating',
        'hazardous_area_certification' => 'Hazardous Area Certification',
        'response_time'                => 'Response Time',
        'operating_temperature'        => 'Operating Temperature',
    ],

    'Pressure Gauges' => [
        'measurement_min'       => 'Measurement Min',
        'measurement_max'       => 'Measurement Max',
        'measurement_unit'      => 'Measurement Unit',
        'dial_size'             => 'Dial Size',
        'connection_size'       => 'Connection Size',
        'connection_type'       => 'Connection Type',
        'case_material'         => 'Case Material',
        'wetted_material'       => 'Wetted Material',
        'accuracy'              => 'Accuracy',
        'mounting_type'         => 'Mounting Type',
        'pressure_element_type' => 'Pressure Element Type',
        'enclosure_rating'      => 'Enclosure Rating',
    ],

    'Temperature Gauges' => [
        'temperature_min'   => 'Temperature Min',
        'temperature_max'   => 'Temperature Max',
        'measurement_unit'  => 'Measurement Unit',
        'dial_size'         => 'Dial Size',
        'stem_length'       => 'Stem Length',
        'stem_diameter'     => 'Stem Diameter',
        'connection_type'   => 'Connection Type',
        'accuracy'          => 'Accuracy',
        'case_material'     => 'Case Material',
        'mounting_type'     => 'Mounting Type',
        'sensor_type'       => 'Sensor Type',
    ],

];
