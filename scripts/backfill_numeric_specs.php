<?php

/*
 * One-off backfill: derive the machine-comparable `_numeric` index for
 * products saved before SpecValueParser existed.
 * Run: php artisan tinker --execute="require 'scripts/backfill_numeric_specs.php';"
 */

use App\Models\Product;
use App\Services\Catalogue\SpecValueParser;

$parser  = app(SpecValueParser::class);
$updated = 0;

Product::withTrashed()->whereNotNull('specifications')->chunkById(100, function ($products) use ($parser, &$updated) {
    foreach ($products as $product) {
        $specs = $product->specifications;
        if (!is_array($specs)) continue;

        unset($specs['_numeric']);
        $numeric = $parser->derive($specs);
        if ($numeric) $specs['_numeric'] = $numeric;

        if ($specs !== $product->specifications) {
            $product->timestamps = false;
            $product->update(['specifications' => $specs]);
            $updated++;
        }
    }
});

echo "Backfilled _numeric for {$updated} products.\n";
