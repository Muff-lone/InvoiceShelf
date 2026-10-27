<?php

use App\Domains\Accounts\Models\Company;
use App\Domains\Accounts\Models\CompanySetting;
use App\Domains\Accounts\Models\User;
use App\Domains\Contacts\Models\Customer;
use App\Domains\Money\Models\Currency;
use App\Domains\Sales\Models\Invoice;
use App\Domains\Sales\Support\PaymentQrService;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

    $this->user = User::find(1);
    $this->company = $this->user->companies()->first();
    $this->companyId = $this->company->id;

    Sanctum::actingAs($this->user, ['*']);
});

test('company settings persist and expose Czech accounting fields (ico, vat_id, vat_payer, registry_info)', function () {
    $response = $this->withHeaders(['company' => $this->companyId])
        ->putJson('/api/v1/company', [
            'name' => 'Orion Czech s.r.o.',
            'ico' => '12345678',
            'vat_id' => 'CZ12345678',
            'vat_payer' => false,
            'registry_info' => 'Společnost je zapsána v obchodním rejstříku vedeném Městským soudem v Praze, oddíl C, vložka 98765.',
            'bank_name' => 'Fio banka, a.s.',
            'bank_account_prefix' => '19',
            'bank_account_number' => '2000145399',
            'bank_code' => '2010',
            'bank_iban' => 'CZ6520100000192000145399',
            'bank_bic' => 'FIOBCZPPXXX',
            'address' => [
                'country_id' => 1,
                'city' => 'Praha',
                'zip' => '11000',
                'address_street_1' => 'Václavské náměstí 1',
            ],
        ]);

    $response->assertOk()
        ->assertJsonPath('data.ico', '12345678')
        ->assertJsonPath('data.tax_id', '12345678')
        ->assertJsonPath('data.vat_id', 'CZ12345678')
        ->assertJsonPath('data.vat_payer', false)
        ->assertJsonPath('data.registry_info', 'Společnost je zapsána v obchodním rejstříku vedeném Městským soudem v Praze, oddíl C, vložka 98765.');

    $company = Company::find($this->companyId);
    expect($company->isVatPayer())->toBeFalse()
        ->and($company->ico)->toBe('12345678')
        ->and($company->registry_info)->toContain('oddíl C, vložka 98765');
});

test('customer persists and returns both ico and tax_id (DIČ)', function () {
    $currencyId = Currency::first()->id;

    $response = $this->withHeaders(['company' => $this->companyId])
        ->postJson('/api/v1/customers', [
            'name' => 'Odběratel Alfa a.s.',
            'email' => 'info@alfa.cz',
            'currency_id' => $currencyId,
            'ico' => '87654321',
            'tax_id' => 'CZ87654321',
            'enable_portal' => false,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.ico', '87654321')
        ->assertJsonPath('data.tax_id', 'CZ87654321');
});

test('invoice persists DUZP, variable_symbol, constant_symbol, specific_symbol and payment_method and generates SPD QR payload', function () {
    $czk = Currency::firstOrCreate(
        ['code' => 'CZK'],
        [
            'name' => 'Czech Koruna',
            'symbol' => 'Kč',
            'precision' => 2,
            'thousand_separator' => ' ',
            'decimal_separator' => ',',
            'swap_currency_symbol' => true,
        ]
    );

    CompanySetting::setSettings([
        'currency' => $czk->id,
        'tax_per_item' => 'NO',
        'discount_per_item' => 'NO',
    ], $this->companyId);

    $this->company->update([
        'ico' => '12345678',
        'tax_id' => '12345678',
        'vat_id' => 'CZ12345678',
        'vat_payer' => true,
        'registry_info' => 'Spisová značka C 12345 vedená u MS v Praze',
        'bank_iban' => 'CZ6520100000192000145399',
        'bank_account_number' => '2000145399',
        'bank_code' => '2010',
    ]);

    $customer = Customer::factory()->create([
        'company_id' => $this->companyId,
        'currency_id' => $czk->id,
        'ico' => '87654321',
        'tax_id' => 'CZ87654321',
    ]);

    $response = $this->withHeaders(['company' => $this->companyId])
        ->postJson('/api/v1/invoices', [
            'invoice_date' => '2026-10-27',
            'due_date' => '2026-11-10',
            'taxable_supply_date' => '2026-10-25',
            'invoice_number' => 'FA-2026-0042',
            'variable_symbol' => '20260099',
            'constant_symbol' => '0308',
            'specific_symbol' => '1122',
            'payment_method' => 'bank_transfer',
            'customer_id' => $customer->id,
            'currency_id' => $czk->id,
            'template_name' => 'invoice1',
            'discount' => 0,
            'discount_type' => 'fixed',
            'discount_val' => 0,
            'sub_total' => 100000,
            'total' => 100000,
            'tax' => 0,
            'items' => [
                [
                    'name' => 'Konzultace',
                    'quantity' => 1,
                    'price' => 100000,
                    'discount_type' => 'fixed',
                    'discount_val' => 0,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 100000,
                ],
            ],
            'taxes' => [],
        ]);

    $response->assertOk()
        ->assertJsonPath('data.taxable_supply_date', '2026-10-25')
        ->assertJsonPath('data.variable_symbol', '20260099')
        ->assertJsonPath('data.constant_symbol', '0308')
        ->assertJsonPath('data.specific_symbol', '1122')
        ->assertJsonPath('data.payment_method', 'bank_transfer');

    $invoice = Invoice::findOrFail($response->json('data.id'));

    $qrService = new PaymentQrService;
    $spdPayload = $qrService->generatePayload($invoice);

    expect($spdPayload)->not->toBeNull()
        ->and($spdPayload)->toContain('SPD*1.0*AM:1000.00*CC:CZK')
        ->and($spdPayload)->toContain('*X-VS:20260099')
        ->and($spdPayload)->toContain('*X-KS:0308')
        ->and($spdPayload)->toContain('*X-SS:1122');

    // Verify standard PDF view contains the new Czech accounting fields
    $html = view('app.pdf.invoice.invoice1', [
        'invoice' => $invoice->fresh(['company', 'customer', 'items', 'taxes']),
        'logo' => null,
        'company_address' => $invoice->getCompanyAddress(),
        'billing_address' => $invoice->getCustomerBillingAddress(),
        'shipping_address' => $invoice->getCustomerShippingAddress(),
        'notes' => $invoice->getNotes(),
        'documentFields' => collect(),
        'customerFields' => collect(),
        'customFields' => collect(),
        'taxTypes' => [],
        'labels' => [],
    ])->render();

    expect($html)->toContain('12345678')
        ->and($html)->toContain('CZ12345678')
        ->and($html)->toContain('Spisová značka C 12345 vedená u MS v Praze')
        ->and($html)->toContain('87654321')
        ->and($html)->toContain('CZ87654321')
        ->and($html)->toContain('20260099')
        ->and($html)->toContain('0308')
        ->and($html)->toContain('1122');
});
