<?php

use App\Services\Catalogue\SpecValueParser;

beforeEach(function () {
    $this->parser = new SpecValueParser();
});

it('parses a kV range with no spaces', function () {
    expect($this->parser->parse('110kV to 400kV'))
        ->toBe(['min' => 110.0, 'max' => 400.0, 'unit' => 'kV']);
});

it('parses a spaced range with trailing unit only', function () {
    expect($this->parser->parse('110 to 400 kV'))
        ->toBe(['min' => 110.0, 'max' => 400.0, 'unit' => 'kV']);
});

it('parses hyphen ranges', function () {
    expect($this->parser->parse('0.09 kW - 1000 kW'))
        ->toBe(['min' => 0.09, 'max' => 1000.0, 'unit' => 'kW']);
    expect($this->parser->parse('10-16 bar'))
        ->toBe(['min' => 10.0, 'max' => 16.0, 'unit' => 'bar']);
});

it('keeps negative temperatures intact', function () {
    expect($this->parser->parse('-40°C to 200°C'))
        ->toBe(['min' => -40.0, 'max' => 200.0, 'unit' => '°C']);
});

it('parses open-ended bounds', function () {
    expect($this->parser->parse('Up to 200 kW'))
        ->toBe(['min' => null, 'max' => 200.0, 'unit' => 'kW']);
    expect($this->parser->parse('From 5 kW'))
        ->toBe(['min' => 5.0, 'max' => null, 'unit' => 'kW']);
    expect($this->parser->parse('5 kW and above'))
        ->toBe(['min' => 5.0, 'max' => null, 'unit' => 'kW']);
});

it('parses single values as a point range', function () {
    expect($this->parser->parse('415 V'))
        ->toBe(['min' => 415.0, 'max' => 415.0, 'unit' => 'V']);
    expect($this->parser->parse('2900 RPM'))
        ->toBe(['min' => 2900.0, 'max' => 2900.0, 'unit' => 'RPM']);
    expect($this->parser->parse('50'))
        ->toBe(['min' => 50.0, 'max' => 50.0, 'unit' => null]);
});

it('normalizes unit casing', function () {
    expect($this->parser->parse('110kv to 400kv')['unit'])->toBe('kV');
    expect($this->parser->parse('75 KW')['unit'])->toBe('kW');
});

it('swaps inverted bounds', function () {
    expect($this->parser->parse('400kV to 110kV'))
        ->toBe(['min' => 110.0, 'max' => 400.0, 'unit' => 'kV']);
});

it('handles thousands separators', function () {
    expect($this->parser->parse('1,000 kW to 2,500 kW'))
        ->toBe(['min' => 1000.0, 'max' => 2500.0, 'unit' => 'kW']);
});

it('returns null for pure text', function () {
    expect($this->parser->parse('Carbon Steel'))->toBeNull();
    expect($this->parser->parse('Flanged'))->toBeNull();
    expect($this->parser->parse(''))->toBeNull();
    expect($this->parser->parse(null))->toBeNull();
});

it('leaves multi-value lists display-only', function () {
    expect($this->parser->parse('NPS 8, NPS 10, NPS 12'))->toBeNull();
    expect($this->parser->parse('230/400 V'))->toBeNull();
    expect($this->parser->parse('Class IV (FCI 70-2)'))->toBeNull();
});

it('does not turn codes into numbers', function () {
    // Text-leading values like IP ratings are not comparable quantities.
    expect($this->parser->parse('IP55'))->toBeNull();
    expect($this->parser->parse('Class 150'))->toBeNull();
});

it('derives a numeric index for a spec map', function () {
    $idx = $this->parser->derive([
        'voltage'   => '110kV to 400kV',
        'material'  => 'Carbon Steel',
        'power_kw'  => '75 kW',
        'extra'     => ['Warranty' => '2 years'],
    ]);

    expect($idx)->toHaveKeys(['voltage', 'power_kw'])
        ->and($idx)->not->toHaveKey('material')
        ->and($idx)->not->toHaveKey('extra')
        ->and($idx['voltage'])->toBe(['min' => 110.0, 'max' => 400.0, 'unit' => 'kV']);
});
