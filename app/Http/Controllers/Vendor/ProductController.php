<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateProductRequest;
use App\Models\Product;
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
        // Only the planned fields of the selected category are accepted —
        // unknown keys (forged or stale from a category switch) are dropped.
        $allowed = array_keys((array) config('category_fields.' . $request->input('category'), []));

        $specs = [];
        foreach ((array) $request->input('specs', []) as $k => $v) {
            if (!in_array($k, $allowed, true)) continue;
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

        // Machine-comparable mirror of the values ("110kV to 400kV" →
        // min 110 / max 400 / unit kV) — comparisons use numbers, not strings.
        $numeric = app(\App\Services\Catalogue\SpecValueParser::class)->derive($specs);
        if ($numeric) {
            $specs['_numeric'] = $numeric;
        }

        return $specs ?: null;
    }

    // ─── Store: single product ────────────────────────────────────────
    public function store(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys((array) config('category_fields', [])))],
            'brand'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'category.in' => 'Please choose a product type from the list.',
        ]);

        $data = $request->only(['name', 'category', 'brand', 'description']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products/images', 'public');
        }

        $data['specifications'] = $this->buildSpecs($request);
        $data['status']         = 'active';

        $vendor->products()->create($data);

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
