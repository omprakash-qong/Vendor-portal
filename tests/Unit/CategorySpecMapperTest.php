<?php

use App\Services\Catalogue\CategorySpecMapper;
use App\Services\Catalogue\SpecValueParser;

beforeEach(function () {
    $this->mapper = new CategorySpecMapper();
});

it('resolves fuzzy category names to canonical config keys', function () {
    expect($this->mapper->resolveCategory('IE4 Motors'))->toBe('Motors')
        ->and($this->mapper->resolveCategory('Centrifugal Pumps'))->toBe('Pumps')
        ->and($this->mapper->resolveCategory('valve'))->toBe('Valves');
});

it('rejects categories outside the planned catalogue', function () {
    expect($this->mapper->resolveCategory('Gearboxes'))->toBeNull()
        ->and($this->mapper->resolveCategory('Other'))->toBeNull()
        ->and($this->mapper->resolveCategory(null))->toBeNull();
});

it('discards unmapped scraped junk on website imports', function () {
    $specs = $this->mapper->normalize('Motors', [
        'Voltage'        => '415 V',
        'Frame Size'     => '132M',
        // junk rows that real pages carry:
        'Downloads'      => 'Datasheet PDF',
        'Country'        => 'India',
        'Subscribe'      => 'Enter your email',
        'Related Links'  => 'Home | About',
    ], keepExtras: false);

    expect($specs)->toBe(['voltage' => '415 V', 'frame_size' => '132M']);
});

it('keeps vendor-typed extras when manually entered', function () {
    $specs = $this->mapper->normalize('Motors', ['Warranty' => '2 years'], keepExtras: true);
    expect($specs['extra'])->toBe(['Warranty' => '2 years']);
});

it('splits scraped range labels into min and max', function () {
    $specs = $this->mapper->normalize('Motors', ['Power Range' => '0.09 kW to 1000 kW'], keepExtras: false);
    expect($specs['power_min_kw'])->toBe('0.09 kW')
        ->and($specs['power_max_kw'])->toBe('1000 kW');
});

it('produces a numeric index for a scraped voltage range end-to-end', function () {
    // The exact case from the field: "110kV to 400kV" must become numbers.
    $specs = $this->mapper->normalize('Motors', [
        'Voltage'    => '110kV to 400kV',
        'Frame Size' => '132M',
    ], keepExtras: false);

    $numeric = (new SpecValueParser())->derive($specs);

    expect($numeric['voltage'])->toBe(['min' => 110.0, 'max' => 400.0, 'unit' => 'kV']);
});
