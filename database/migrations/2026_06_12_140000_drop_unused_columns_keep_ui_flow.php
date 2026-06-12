<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Column-level cleanup to match the live vendor UI flow. Removes columns that
 * are never written by any reachable form/controller/import and never shown:
 *
 *   products        : sku, short_description, long_description, datasheet_url
 *                     (only the now-removed file-extraction / staging path used them)
 *   quotations      : price, lead_time        (legacy RFQ fields; store() never sets them)
 *   vendor_profiles : pan, cin, bank_details, reviewer_notes  (not in onboarding form / admin view)
 *   import_jobs     : file_name, file_path     (file upload removed; website import only)
 *
 * KEPT on products: catalogue_url + import_source — internal-only, required by the
 * website-import resume/dedup pipeline (not "extra" junk).
 */
return new class extends Migration
{
    private array $map = [
        'products'        => ['sku', 'short_description', 'long_description', 'datasheet_url'],
        'quotations'      => ['price', 'lead_time'],
        'vendor_profiles' => ['pan', 'cin', 'bank_details', 'reviewer_notes'],
        'import_jobs'     => ['file_name', 'file_path'],
    ];

    public function up(): void
    {
        foreach ($this->map as $table => $columns) {
            $present = array_values(array_filter($columns, fn ($c) => Schema::hasColumn($table, $c)));
            if ($present) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn($present));
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->string('sku', 100)->nullable();
            $t->text('short_description')->nullable();
            $t->longText('long_description')->nullable();
            $t->string('datasheet_url', 500)->nullable();
        });
        Schema::table('quotations', function (Blueprint $t) {
            $t->decimal('price', 12, 2)->nullable();
            $t->string('lead_time')->nullable();
        });
        Schema::table('vendor_profiles', function (Blueprint $t) {
            $t->string('pan')->nullable();
            $t->string('cin')->nullable();
            $t->string('bank_details')->nullable();
            $t->text('reviewer_notes')->nullable();
        });
        Schema::table('import_jobs', function (Blueprint $t) {
            $t->string('file_name', 255)->nullable();
            $t->string('file_path', 500)->nullable();
        });
    }
};
