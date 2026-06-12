<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trim the schema to ONLY the tables the live vendor UI flow uses:
 *   Dashboard → My Products (+ website Import) → Quotations, plus
 *   auth / onboarding / admin-approval.
 *
 * Drops 13 tables belonging to features that are not reachable from the
 * sidebar (catalogue/PDF extraction, QS matching API, datasheets, RFQs,
 * support tickets, legacy staging, and the config-replaced category_fields).
 *
 * NOTE: this is a destructive cleanup. down() recreates only the structural
 * shells needed to roll back the migration record — it does not restore data.
 */
return new class extends Migration
{
    /** Tables to remove, in FK-safe order (children → parents). */
    private array $drop = [
        // QS Systems matching API
        'qs_match_results',
        'qs_match_requests',
        'qs_api_keys',
        // Catalogue / PDF extraction → variants
        'product_variants',
        'product_series',
        'product_categories',
        'extraction_jobs',
        'vendor_documents',
        // Ancillary vendor features
        'datasheets',
        'rfqs',
        'support_tickets',
        // Legacy / replaced
        'products_staging',
        'category_fields',
    ];

    public function up(): void
    {
        // quotations.rfq_id → rfqs is the only inbound FK from a KEPT table to
        // a dropped one. The column is nullable and unused by QuotationController,
        // so detach and remove it before dropping rfqs.
        if (Schema::hasColumn('quotations', 'rfq_id')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropForeign('quotations_rfq_id_foreign');
                $table->dropColumn('rfq_id');
            });
        }

        Schema::disableForeignKeyConstraints();
        foreach ($this->drop as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Irreversible data-wise. Re-add the quotations.rfq_id column shell so
        // the migration can be rolled back cleanly; the dropped feature tables
        // are not recreated here (restore from a backup if needed).
        if (!Schema::hasColumn('quotations', 'rfq_id')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->unsignedBigInteger('rfq_id')->nullable()->after('id');
            });
        }
    }
};
