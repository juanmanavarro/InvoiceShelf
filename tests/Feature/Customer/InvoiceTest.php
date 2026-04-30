<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

    $customer = Customer::factory()->create();

    Sanctum::actingAs(
        $customer,
        ['*'],
        'customer'
    );
});

test('get customer invoices', function () {
    $customer = Auth::guard('customer')->user();

    getJson("api/v1/{$customer->company->slug}/customer/invoices?page=1")->assertOk();
});

test('get customer invoices orders by invoice date descending by default', function () {
    $customer = Auth::guard('customer')->user();

    $recentInvoice = Invoice::factory()->create([
        'company_id' => $customer->company_id,
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_SENT,
        'invoice_number' => 'CINV-DATE-RECENT',
        'invoice_date' => '2024-02-01',
    ]);

    $olderInvoice = Invoice::factory()->create([
        'company_id' => $customer->company_id,
        'customer_id' => $customer->id,
        'status' => Invoice::STATUS_SENT,
        'invoice_number' => 'CINV-DATE-OLDER',
        'invoice_date' => '2024-01-01',
    ]);

    $response = getJson("api/v1/{$customer->company->slug}/customer/invoices?page=1&limit=20");

    expect(collect($response->json('data'))->pluck('id')->take(2)->all())
        ->toBe([$recentInvoice->id, $olderInvoice->id]);
});

test('get customer invoice', function () {
    $customer = Auth::guard('customer')->user();

    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
    ]);

    getJson("/api/v1/{$customer->company->slug}/customer/invoices/{$invoice->id}")->assertOk();

    $this->assertDatabaseHas('invoices', [
        'template_name' => $invoice['template_name'],
        'invoice_number' => $invoice['invoice_number'],
        'sub_total' => $invoice['sub_total'],
        'discount' => $invoice['discount'],
        'customer_id' => $invoice['customer_id'],
        'total' => $invoice['total'],
        'tax' => $invoice['tax'],
    ]);
});
