<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreProductRequest;
use App\Http\Requests\Vendor\UpdateProductRequest;
use App\Models\Product;
use App\Services\Catalogue\FileValidationService;
use App\Services\Catalogue\PdfExtractionService;
use App\Services\Catalogue\ExcelExtractionService;
use App\Services\Catalogue\CsvExtractionService;
use App\Services\Catalogue\RuleBasedStructuringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $query = Product::where('vendor_profile_id', $vendor->id);

        // Status tab filter
        $tab = $request->input('status', 'all');
        if ($tab === 'inactive') {
            $query->where('status', 'inactive');
        } elseif ($tab === 'active') {
            $query->whereIn('status', ['active', 'published']);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('model_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        // Is an import still bringing products in? Drives the live "Importing…"
        // indicator and lightweight auto-refresh on the page.
        $importing = \App\Models\ImportJob::where('vendor_profile_id', $vendor->id)
            ->whereIn('status', ['queued', 'running'])
            ->exists();

        return view('vendor.products.index', compact('products', 'importing'));
    }

    public function create()
    {
        return view('vendor.products.create', ['categoryFields' => config('category_fields')]);
    }

    /** Build the specifications JSON from category fields + extra rows. */
    private function buildSpecs(Request $request): ?array
    {
        $specs = [];
        foreach ((array) $request->input('specs', []) as $k => $v) {
            $v = is_string($v) ? trim($v) : $v;
            if ($v !== '' && $v !== null) {
                $specs[$k] = $v;
            }
        }

        $names = (array) $request->input('extra_name', []);
        $vals  = (array) $request->input('extra_value', []);
        $extra = [];
        foreach ($names as $i => $n) {
            $n = trim((string) $n);
            $v = trim((string) ($vals[$i] ?? ''));
            if ($n !== '' && $v !== '') {
                $extra[$n] = $v;
            }
        }
        if ($extra) {
            $specs['extra'] = $extra;
        }

        return $specs ?: null;
    }

    // ─── Extract: synchronous PDF/Excel/CSV → products preview ───────
    public function extract(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        $file = $request->file('file');

        // Validate file type via service
        $fileValidator = app(FileValidationService::class);
        $validation = $fileValidator->validate($file);

        if (!$validation['valid']) {
            return response()->json(['error' => $validation['message']], 422);
        }

        $fileType = $validation['type'];

        // Store temp copy for extraction
        $tempPath = $file->store('catalogue/temp', 'local');
        $absPath  = Storage::disk('local')->path($tempPath);

        try {
            // Extract raw content
            $rawExtracted = match ($fileType) {
                'pdf'   => app(PdfExtractionService::class)->extract($absPath),
                'excel' => app(ExcelExtractionService::class)->extract($absPath),
                'csv'   => app(CsvExtractionService::class)->extract($absPath),
                default => throw new \RuntimeException('Unsupported file type: ' . $fileType),
            };

            // Structure with rule-based service
            $profile    = auth()->user()->vendorProfile;
            $vendorName = $profile?->trade_name ?? $profile?->legal_company_name ?? '';
            $structured = app(RuleBasedStructuringService::class)->structure($rawExtracted, $vendorName);

            // Map vendor_series → flat products list
            $products = [];
            foreach ($structured['vendor_series'] ?? [] as $series) {
                $seriesCategory = $series['category'] ?? 'Other';

                foreach ($series['variants'] ?? [] as $variant) {
                    $products[] = $this->variantToProduct($variant, $seriesCategory);
                }
            }

            return response()->json([
                'products' => $products,
                'count'    => count($products),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } finally {
            // Clean up temp file
            Storage::disk('local')->delete($tempPath);
        }
    }

    // ─── Map a structured variant → product fields ────────────────────
    private function variantToProduct(array $variant, string $seriesCategory): array
    {
        $name     = $variant['variant_name'] ?? 'Unknown Product';
        $category = $seriesCategory;

        // Build a human-readable description from key spec fields
        $descParts = [];
        if (!empty($variant['power_kw']))     $descParts[] = $variant['power_kw'] . 'kW';
        if (!empty($variant['poles']))        $descParts[] = $variant['poles'] . ' Pole';
        if (!empty($variant['size_mm']))      $descParts[] = 'DN' . $variant['size_mm'];
        elseif (!empty($variant['size_inch'])) $descParts[] = $variant['size_inch'] . '"';
        if (!empty($variant['pressure_bar'])) $descParts[] = $variant['pressure_bar'] . ' bar';
        if (!empty($variant['flow_m3h']))     $descParts[] = $variant['flow_m3h'] . ' m³/h';
        if (!empty($variant['voltage_v']))    $descParts[] = $variant['voltage_v'] . 'V';

        // Pull frame/efficiency from nested specifications
        $specs = $variant['specifications'] ?? [];
        if (!empty($specs['frame_size']))      $descParts[] = 'Frame ' . $specs['frame_size'];
        if (!empty($specs['efficiency_class'])) $descParts[] = strtoupper($specs['efficiency_class']);

        $description = implode(' · ', $descParts) ?: null;

        // Build full specifications block (tech fields + nested specs)
        $fullSpecs = $specs;
        foreach (['power_kw', 'poles', 'size_mm', 'size_inch', 'pressure_bar', 'flow_m3h', 'voltage_v', 'equipment_type'] as $k) {
            if (isset($variant[$k]) && $variant[$k] !== null) {
                $fullSpecs[$k] = $variant[$k];
            }
        }
        if (!empty($variant['industry_tags']))   $fullSpecs['industry_tags']   = $variant['industry_tags'];
        if (!empty($variant['capability_tags'])) $fullSpecs['capability_tags'] = $variant['capability_tags'];

        return [
            'name'           => $name,
            'category'       => $category,
            'description'    => $description,
            'sku'            => null,
            'specifications' => empty($fullSpecs) ? null : $fullSpecs,
        ];
    }

    // ─── Store: single product OR bulk array ──────────────────────────
    public function store(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        // ── Bulk import path ──────────────────────────────────────────
        if ($request->has('products') && is_array($request->input('products'))) {
            $request->validate([
                'products'                => 'required|array|min:1',
                'products.*.name'         => 'required|string|max:255',
                'products.*.category'     => 'required|string|max:255',
                'products.*.sku'          => 'nullable|string|max:100',
                'products.*.description'  => 'nullable|string',
                'products.*.specifications' => 'nullable',
            ]);

            $count = 0;
            foreach ($request->input('products') as $item) {
                $specs = $item['specifications'] ?? null;
                // Decode if passed as JSON string
                if (is_string($specs) && $specs !== '') {
                    $decoded = json_decode($specs, true);
                    $specs = is_array($decoded) ? $decoded : null;
                }

                $vendor->products()->create([
                    'name'           => $item['name'],
                    'category'       => $item['category'],
                    'sku'            => $item['sku'] ?? null,
                    'description'    => $item['description'] ?? null,
                    'specifications' => $specs,
                ]);
                $count++;
            }

            return redirect()->route('vendor.products.index')
                ->with('success', "{$count} product(s) imported successfully.");
        }

        // ── Single product path ───────────────────────────────────────
        $request->validate([
            'name'              => 'required|string|max:255',
            'category'          => 'required|string|max:255',
            'brand'             => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $data = $request->only(['name', 'category', 'brand', 'short_description', 'description']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products/images', 'public');
        }

        $data['specifications'] = $this->buildSpecs($request);
        $data['status']         = 'active';

        $product = $vendor->products()->create($data);
        app(\App\Services\Catalogue\ProductSpecSync::class)->sync($product);

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product saved and added to your catalogue.');
    }

    public function edit(Product $product)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($product->vendor_profile_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }

        return view('vendor.products.edit', [
            'product'        => $product,
            'categoryFields' => config('category_fields'),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($product->vendor_profile_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products/images', 'public');
        }

        $data['specifications'] = $this->buildSpecs($request);

        // Reviewing & saving a product publishes it to the catalogue.
        $wasInactive = $product->status === 'inactive';
        $data['status'] = 'active';

        $product->update($data);
        app(\App\Services\Catalogue\ProductSpecSync::class)->sync($product);

        $msg = $wasInactive
            ? "'{$product->name}' has been saved and is now active in your catalogue."
            : 'Product saved successfully.';

        return redirect()->route('vendor.products.index')->with('success', $msg);
    }

    public function destroy(Product $product)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($product->vendor_profile_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }

        $product->delete();

        return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully.');
    }
}
