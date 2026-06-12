<?php
 namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

 
class VendorProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'legal_company_name', 'trade_name', 'company_type',
        'reg_address_line1', 'reg_address_line2', 'reg_city', 'reg_state', 'reg_pincode', 'reg_country',
        'same_as_registered',
        'op_address_line1', 'op_address_line2', 'op_city', 'op_state', 'op_pincode', 'op_country',
        'company_website', 'company_logo',
        'primary_name', 'primary_designation', 'primary_phone', 'primary_email',
        'secondary',
        'pan', 'gstin', 'cin', 'incorporation_number', 'msme', 'tax_id_intl', 'msme_certificate',
        'vendor_category', 'vendor_category_other', 'authorization_letter', 'industry_focus', 'industry_focus_other',
        'subdomain_pumps', 'subdomain_compressors', 'subdomain_instruments',
        'subdomain_valves', 'subdomain_turbines', 'subdomain_motors',
        'iso_certified', 'iso_number', 'iso_certificate', 'industry_standards', 'other_standards',
        'quality_certificate_number', 'quality_certificate_file',
        'authorized_brands', 'distribution_region', 'inventory_capability', 'warehouse_availability', 'dealer_certificate',
        'company_brochure', 'incorporation_cert', 'bank_details', 'additional_certs',
        'terms_accepted', 'data_accurate',
        'submission_status', 'submitted_at', 'reviewed_at', 'reviewer_notes', 'admin_notes',
    ];
 
    protected $casts = [
        'vendor_category'          => 'array',
        'industry_focus'           => 'array',
        'industry_standards'       => 'array',
        'secondary'                => 'array',
        'subdomain_pumps'          => 'array',
        'subdomain_compressors'    => 'array',
        'subdomain_instruments'    => 'array',
        'subdomain_valves'         => 'array',
        'subdomain_turbines'       => 'array',
        'subdomain_motors'         => 'array',
        'additional_certs'         => 'array',
        'same_as_registered'       => 'boolean',
        'terms_accepted'           => 'boolean',
        'data_accurate'            => 'boolean',
        'submitted_at'             => 'datetime',
        'reviewed_at'              => 'datetime',
    ];
 
    public function user() { return $this->belongsTo(User::class); }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function datasheets()
    {
        return $this->hasMany(Datasheet::class);
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}