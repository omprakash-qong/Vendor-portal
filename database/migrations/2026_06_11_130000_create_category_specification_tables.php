<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Category (config key) => normalized spec table name. */
    private array $map = [
        'Motors'                => 'motor_specifications',
        'Pumps'                 => 'pump_specifications',
        'Valves'                => 'valve_specifications',
        'Blowers'               => 'blower_specifications',
        'Compressors'           => 'compressor_specifications',
        'Pressure Transmitters' => 'pressure_transmitter_specifications',
        'Pressure Gauges'       => 'pressure_gauge_specifications',
        'Temperature Gauges'    => 'temperature_gauge_specifications',
    ];

    public function up(): void
    {
        foreach ($this->map as $category => $table) {
            $fields = array_keys(config("category_fields.$category", []));

            Schema::create($table, function (Blueprint $t) use ($fields) {
                $t->id();
                $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                foreach ($fields as $field) {
                    $t->string($field, 500)->nullable();
                }
                $t->timestamps();
                $t->unique('product_id');   // one spec row per product
            });
        }

        // Unknown / vendor-added fields not yet promoted to a column.
        Schema::create('product_additional_specifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->string('field_name');
            $t->text('field_value')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_additional_specifications');
        foreach (array_reverse($this->map) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
