<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidate specification storage to a single source of truth.
 *
 * Before: every product's specs were written to THREE places —
 *   products.specifications (JSON), one of 8 per-category spec tables,
 *   and product_additional_specifications. Only the JSON was ever read
 *   (forms, views, import). The mirrors were redundant storage: extra
 *   writes, update-anomaly risk, and join-heavy queries for no benefit.
 *
 * After: products.specifications JSON is the only spec store. Product
 * queries are single-table — no joins. The per-category field schema is
 * enforced at the application boundary (config/category_fields.php drives
 * the forms, CategorySpecMapper + validation whitelist the keys).
 */
return new class extends Migration
{
    private array $drop = [
        'product_additional_specifications',
        'motor_specifications',
        'pump_specifications',
        'valve_specifications',
        'blower_specifications',
        'compressor_specifications',
        'pressure_transmitter_specifications',
        'pressure_gauge_specifications',
        'temperature_gauge_specifications',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ($this->drop as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Data lived redundantly in products.specifications all along, so no
        // shells are recreated; restore from a backup if the mirrors are ever
        // needed again.
    }
};
