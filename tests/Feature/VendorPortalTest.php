<?php

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Product;
use App\Models\Datasheet;
use App\Models\Rfq;
use App\Models\Quotation;
use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ─── PUBLIC PAGES ────────────────────────────────────────────────────────────

test('landing page loads', function () {
    $this->get('/')->assertStatus(200);
});

test('vendor application form loads', function () {
    $this->get('/apply')->assertStatus(200)->assertSee('Vendor Onboarding');
});

test('success page loads', function () {
    $this->get('/apply/success')->assertStatus(200)->assertSee('Application Submitted');
});

test('login page loads', function () {
    $this->get('/login')->assertStatus(200)->assertSee('Sign In');
});

// ─── VENDOR APPLICATION SUBMISSION ───────────────────────────────────────────

test('vendor can submit application form and data saves to database', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('incorporation.pdf', 100, 'application/pdf');

    $this->post('/apply', [
        'legal_company_name'  => 'Acme Industries Ltd',
        'trade_name'          => 'Acme',
        'company_type'        => 'Private Limited',
        'company_website'     => 'https://acme.com',
        'reg_address_line1'   => '10 Industrial Park',
        'reg_city'            => 'Mumbai',
        'reg_state'           => 'Maharashtra',
        'reg_pincode'         => '400001',
        'reg_country'         => 'India',
        'primary_name'        => 'John Doe',
        'primary_designation' => 'Director',
        'primary_phone'       => '+91 9876543210',
        'primary_email'       => 'john@acme.com',
        'gstin'               => 'GST12345678901',
        'incorporation_number'=> 'U17110MH2024',
        'vendor_category'     => ['Manufacturer'],
        'industry_focus'      => ['Oil & Gas'],
        'incorporation_cert'  => $file,
        'terms_accepted'      => '1',
        'data_accurate'       => '1',
    ])->assertRedirect('/apply/success');

    $this->assertDatabaseHas('vendor_profiles', [
        'legal_company_name' => 'Acme Industries Ltd',
        'primary_email'      => 'john@acme.com',
        'submission_status'  => 'pending_review',
        'gstin'              => 'GST12345678901',
        'incorporation_number' => 'U17110MH2024',
    ]);
});

test('vendor application fails without required fields', function () {
    $this->post('/apply', [])->assertSessionHasErrors([
        'legal_company_name', 'company_type', 'primary_email',
        'gstin', 'vendor_category', 'terms_accepted',
    ]);
});

test('vendor application rejects personal email', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('inc.pdf', 100, 'application/pdf');

    $this->post('/apply', [
        'legal_company_name'  => 'Test Co',
        'company_type'        => 'Private Limited',
        'reg_address_line1'   => '1 Test St',
        'reg_city'            => 'Delhi',
        'reg_state'           => 'Delhi',
        'reg_pincode'         => '110001',
        'reg_country'         => 'India',
        'primary_name'        => 'Test',
        'primary_designation' => 'CEO',
        'primary_phone'       => '+91 9000000000',
        'primary_email'       => 'test@gmail.com',  // personal email - should be rejected
        'gstin'               => 'GST12345678901',
        'vendor_category'     => ['Manufacturer'],
        'industry_focus'      => ['Oil & Gas'],
        'incorporation_cert'  => $file,
        'terms_accepted'      => '1',
        'data_accurate'       => '1',
    ])->assertSessionHasErrors(['primary_email']);
});

test('vendor application rejects invalid phone number', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('inc.pdf', 100, 'application/pdf');

    $this->post('/apply', [
        'legal_company_name'  => 'Test Co',
        'company_type'        => 'Private Limited',
        'reg_address_line1'   => '1 Test St',
        'reg_city'            => 'Delhi',
        'reg_state'           => 'Delhi',
        'reg_pincode'         => '110001',
        'reg_country'         => 'India',
        'primary_name'        => 'Test',
        'primary_designation' => 'CEO',
        'primary_phone'       => 'abc-invalid',  // invalid phone
        'primary_email'       => 'ceo@testco.com',
        'gstin'               => 'GST12345678901',
        'vendor_category'     => ['Manufacturer'],
        'industry_focus'      => ['Oil & Gas'],
        'incorporation_cert'  => $file,
        'terms_accepted'      => '1',
        'data_accurate'       => '1',
    ])->assertSessionHasErrors(['primary_phone']);
});

test('vendor application rejects invalid GST format', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('inc.pdf', 100, 'application/pdf');

    $this->post('/apply', [
        'legal_company_name'  => 'Test Co',
        'company_type'        => 'Private Limited',
        'reg_address_line1'   => '1 Test St',
        'reg_city'            => 'Delhi',
        'reg_state'           => 'Delhi',
        'reg_pincode'         => '110001',
        'reg_country'         => 'India',
        'primary_name'        => 'Test',
        'primary_designation' => 'CEO',
        'primary_phone'       => '+91 9000000000',
        'primary_email'       => 'ceo@testco.com',
        'gstin'               => 'AB',  // too short - min 5 chars
        'vendor_category'     => ['Manufacturer'],
        'industry_focus'      => ['Oil & Gas'],
        'incorporation_cert'  => $file,
        'terms_accepted'      => '1',
        'data_accurate'       => '1',
    ])->assertSessionHasErrors(['gstin']);
});

test('duplicate active application is blocked', function () {
    VendorProfile::factory()->create([
        'primary_email'     => 'ceo@dupco.com',
        'submission_status' => 'pending_review',
    ]);

    Storage::fake('public');
    $file = UploadedFile::fake()->create('inc.pdf', 100, 'application/pdf');

    $this->post('/apply', [
        'legal_company_name'  => 'Dup Co',
        'company_type'        => 'Private Limited',
        'reg_address_line1'   => '1 St',
        'reg_city'            => 'Mumbai',
        'reg_state'           => 'Maharashtra',
        'reg_pincode'         => '400001',
        'reg_country'         => 'India',
        'primary_name'        => 'CEO',
        'primary_designation' => 'CEO',
        'primary_phone'       => '+91 9000000001',
        'primary_email'       => 'ceo@dupco.com',
        'gstin'               => 'GST12345678901',
        'vendor_category'     => ['Manufacturer'],
        'industry_focus'      => ['Oil & Gas'],
        'incorporation_cert'  => $file,
        'terms_accepted'      => '1',
        'data_accurate'       => '1',
    ])->assertSessionHasErrors(['primary_email']);
});

// ─── AUTHENTICATION ───────────────────────────────────────────────────────────

test('super admin can login and is redirected to admin panel', function () {
    $admin = User::factory()->create([
        'email'    => 'admin@qong.com',
        'password' => bcrypt('password'),
        'role'     => 'super_admin',
    ]);

    $this->post('/login', ['email' => 'admin@qong.com', 'password' => 'password'])
         ->assertRedirect('/admin/vendors');
});

test('vendor user can login and is redirected to vendor dashboard', function () {
    $vendor = User::factory()->create([
        'email'    => 'vendor@corp.com',
        'password' => bcrypt('password'),
        'role'     => 'vendor',
    ]);

    $this->post('/login', ['email' => 'vendor@corp.com', 'password' => 'password'])
         ->assertRedirect('/vendor');
});

test('wrong password is rejected', function () {
    User::factory()->create(['email' => 'user@corp.com', 'password' => bcrypt('correct')]);

    $this->post('/login', ['email' => 'user@corp.com', 'password' => 'wrong'])
         ->assertSessionHasErrors(['email']);
});

// ─── ADMIN ACCESS CONTROL ─────────────────────────────────────────────────────

test('guest cannot access admin panel', function () {
    $this->get('/admin/vendors')->assertRedirect('/login');
});

test('vendor cannot access admin panel', function () {
    $vendor = User::factory()->create(['role' => 'vendor']);
    $this->actingAs($vendor)->get('/admin/vendors')->assertStatus(403);
});

test('admin can access vendor list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get('/admin/vendors')->assertStatus(200);
});

// ─── VENDOR DASHBOARD ACCESS CONTROL ─────────────────────────────────────────

test('guest cannot access vendor dashboard', function () {
    $this->get('/vendor')->assertRedirect('/login');
});

test('vendor can access their dashboard', function () {
    $vendor = User::factory()->create(['role' => 'vendor']);
    $this->actingAs($vendor)->get('/vendor')->assertStatus(200);
});

test('admin cannot access vendor dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get('/vendor')->assertStatus(403);
});

// ─── VENDOR DASHBOARD MODULES ──────────────────────────────────────────────────

test('vendor can manage products', function () {
    $user = User::factory()->create(['role' => 'vendor']);
    $vendor = VendorProfile::factory()->create(['user_id' => $user->id, 'submission_status' => 'approved']);

    // 1. Add Product
    Storage::fake('public');
    $image = UploadedFile::fake()->create('product.jpg', 100, 'image/jpeg');

    $this->actingAs($user)->post('/vendor/products', [
        'name' => 'Test Centrifugal Pump',
        'category' => 'Pumps',
        'description' => 'Test description',
        'image' => $image,
    ])->assertRedirect('/vendor/products');

    $this->assertDatabaseHas('products', [
        'vendor_profile_id' => $vendor->id,
        'name' => 'Test Centrifugal Pump',
        'category' => 'Pumps',
    ]);

    $product = Product::first();

    // 2. Edit Product
    $this->actingAs($user)->post("/vendor/products/{$product->id}", [
        'name' => 'Updated Pump Name',
        'category' => 'Pumps',
        'description' => 'Updated description',
    ])->assertRedirect('/vendor/products');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Pump Name',
    ]);

    // 3. Delete Product
    $this->actingAs($user)->delete("/vendor/products/{$product->id}")
         ->assertRedirect('/vendor/products');

    $this->assertSoftDeleted('products', [
        'id' => $product->id,
    ]);
});

test('vendor can manage datasheets', function () {
    $user = User::factory()->create(['role' => 'vendor']);
    $vendor = VendorProfile::factory()->create(['user_id' => $user->id, 'submission_status' => 'approved']);
    $product = Product::create([
        'vendor_profile_id' => $vendor->id,
        'name' => 'Test Product',
        'category' => 'Pumps',
    ]);

    // 1. Upload Datasheet
    Storage::fake('public');
    $pdf = UploadedFile::fake()->create('spec.pdf', 500, 'application/pdf');

    $this->actingAs($user)->post('/vendor/datasheets', [
        'product_id' => $product->id,
        'name' => 'Test Datasheet',
        'pdf' => $pdf,
    ])->assertRedirect('/vendor/datasheets');

    $this->assertDatabaseHas('datasheets', [
        'vendor_profile_id' => $vendor->id,
        'product_id' => $product->id,
        'name' => 'Test Datasheet',
    ]);

    $datasheet = Datasheet::first();

    // 2. Replace Datasheet
    $newPdf = UploadedFile::fake()->create('new_spec.pdf', 600, 'application/pdf');
    $this->actingAs($user)->post("/vendor/datasheets/{$datasheet->id}/replace", [
        'product_id' => $product->id,
        'name' => 'Replaced Datasheet Name',
        'pdf' => $newPdf,
    ])->assertRedirect('/vendor/datasheets');

    $this->assertDatabaseHas('datasheets', [
        'id' => $datasheet->id,
        'name' => 'Replaced Datasheet Name',
    ]);

    // 3. Delete Datasheet
    $this->actingAs($user)->delete("/vendor/datasheets/{$datasheet->id}")
         ->assertRedirect('/vendor/datasheets');

    $this->assertSoftDeleted('datasheets', [
        'id' => $datasheet->id,
    ]);
});

test('vendor can view RFQs and submit quotation', function () {
    $user = User::factory()->create(['role' => 'vendor']);
    $vendor = VendorProfile::factory()->create(['user_id' => $user->id, 'submission_status' => 'approved']);
    
    $rfq = Rfq::create([
        'vendor_profile_id' => $vendor->id,
        'rfq_number' => 'RFQ-TEST-999',
        'product_name' => 'Test Pumps Required',
        'description' => 'Test RFQ Description',
    ]);

    // 1. View RFQ
    $this->actingAs($user)->get("/vendor/rfqs/{$rfq->id}")
         ->assertStatus(200)
         ->assertSee('RFQ-TEST-999')
         ->assertSee('Test Pumps Required');

    // 2. Submit Quotation
    Storage::fake('public');
    $pdf = UploadedFile::fake()->create('quote_prop.pdf', 500, 'application/pdf');

    $this->actingAs($user)->post("/vendor/rfqs/{$rfq->id}/quote", [
        'price' => 5400.50,
        'lead_time' => '3 Weeks',
        'remarks' => 'Test quotation remarks',
        'attachment' => $pdf,
    ])->assertRedirect("/vendor/rfqs/{$rfq->id}");

    $this->assertDatabaseHas('quotations', [
        'rfq_id' => $rfq->id,
        'vendor_profile_id' => $vendor->id,
        'price' => 5400.50,
        'lead_time' => '3 Weeks',
    ]);
});

test('vendor can raise support ticket', function () {
    $user = User::factory()->create(['role' => 'vendor']);
    $vendor = VendorProfile::factory()->create(['user_id' => $user->id, 'submission_status' => 'approved']);

    $this->actingAs($user)->post('/vendor/support', [
        'subject' => 'Test Ticket Subject',
        'description' => 'Test Ticket Description',
    ])->assertRedirect('/vendor/support');

    $this->assertDatabaseHas('support_tickets', [
        'vendor_profile_id' => $vendor->id,
        'subject' => 'Test Ticket Subject',
        'description' => 'Test Ticket Description',
        'status' => 'open',
    ]);
});
