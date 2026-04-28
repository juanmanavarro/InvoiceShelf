<?php

use App\Http\Requests\InvoicesRequest;
use App\Models\Address;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
});

test('invoice has many invoice items', function () {
    $invoice = Invoice::factory()->hasItems(5)->create();

    $this->assertCount(5, $invoice->items);

    $this->assertTrue($invoice->items()->exists());
});

test('invoice has many taxes', function () {
    $invoice = Invoice::factory()->hasTaxes(5)->create();

    $this->assertCount(5, $invoice->taxes);

    $this->assertTrue($invoice->taxes()->exists());
});

test('invoice has many payments', function () {
    $invoice = Invoice::factory()->hasPayments(5)->create();

    $this->assertCount(5, $invoice->payments);

    $this->assertTrue($invoice->payments()->exists());
});

test('invoice belongs to customer', function () {
    $invoice = Invoice::factory()->forCustomer()->create();

    $this->assertTrue($invoice->customer()->exists());
});

test('invoice billing address includes customer tax id', function () {
    $taxId = 'ES12345678Z';
    $customer = Customer::factory()->create([
        'tax_id' => $taxId,
    ]);

    Address::factory()->create([
        'customer_id' => $customer->id,
        'company_id' => $customer->company_id,
        'type' => Address::BILLING_TYPE,
    ]);

    CompanySetting::setSettings([
        'invoice_billing_address_format' => '<h3>{BILLING_ADDRESS_NAME}</h3><p>{BILLING_PHONE}</p>',
    ], $customer->company_id);

    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
        'company_id' => $customer->company_id,
    ]);

    expect($invoice->getCustomerBillingAddress())
        ->toContain($taxId)
        ->not->toContain(__('pdf_tax_id').': ');
});

test('invoice company address renders street and number on the same line', function () {
    $customer = Customer::factory()->create();

    Address::factory()->create([
        'company_id' => $customer->company_id,
        'customer_id' => null,
        'user_id' => null,
        'address_street_1' => 'Calle Mayor',
        'address_street_2' => '12',
    ]);

    CompanySetting::setSettings([
        'invoice_company_address_format' => '<h3><strong>{COMPANY_NAME}</strong></h3><p>{COMPANY_ADDRESS_STREET_1}</p><p>{COMPANY_ADDRESS_STREET_2}</p>',
    ], $customer->company_id);

    $invoice = Invoice::factory()->create([
        'customer_id' => $customer->id,
        'company_id' => $customer->company_id,
    ]);

    expect($invoice->getCompanyAddress())
        ->toContain('Calle Mayor 12')
        ->not->toContain('Calle Mayor<br />12');
});

test('get previous status', function () {
    $invoice = Invoice::factory()->create();

    $status = $invoice->getPreviousStatus();

    $this->assertEquals('DRAFT', $status);
});

test('create invoice', function () {
    $invoice = Invoice::factory()->raw();

    $item = InvoiceItem::factory()->raw();

    $invoice['items'] = [];
    array_push($invoice['items'], $item);

    $invoice['taxes'] = [];
    array_push($invoice['taxes'], Tax::factory()->raw());

    $request = new InvoicesRequest;

    $request->replace($invoice);

    $invoice_number = explode('-', $invoice['invoice_number']);
    $number_attributes['invoice_number'] = $invoice_number[0].'-'.sprintf('%06d', intval($invoice_number[1]));

    $response = Invoice::createInvoice($request);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $response->id,
        'name' => $item['name'],
        'description' => $item['description'],
        'total' => $item['total'],
        'quantity' => $item['quantity'],
        'discount' => $item['discount'],
        'price' => $item['price'],
    ]);

    $this->assertDatabaseHas('invoices', [
        'invoice_number' => $invoice['invoice_number'],
        'sub_total' => $invoice['sub_total'],
        'total' => $invoice['total'],
        'tax' => $invoice['tax'],
        'discount' => $invoice['discount'],
        'notes' => $invoice['notes'],
        'customer_id' => $invoice['customer_id'],
        'template_name' => $invoice['template_name'],
    ]);
});

test('update invoice', function () {
    $invoice = Invoice::factory()->create();

    $newInvoice = Invoice::factory()->raw();

    $item = InvoiceItem::factory()->raw([
        'invoice_id' => $invoice->id,
    ]);

    $tax = Tax::factory()->raw([
        'invoice_id' => $invoice->id,
    ]);

    $newInvoice['items'] = [];
    $newInvoice['taxes'] = [];

    array_push($newInvoice['items'], $item);
    array_push($newInvoice['taxes'], $tax);

    $request = new InvoicesRequest;

    $request->replace($newInvoice);

    $invoice_number = explode('-', $newInvoice['invoice_number']);

    $number_attributes['invoice_number'] = $invoice_number[0].'-'.sprintf('%06d', intval($invoice_number[1]));

    $response = $invoice->updateInvoice($request);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $response->id,
        'name' => $item['name'],
        'description' => $item['description'],
        'total' => $item['total'],
        'quantity' => $item['quantity'],
        'discount' => $item['discount'],
        'price' => $item['price'],
    ]);

    $this->assertDatabaseHas('invoices', [
        'invoice_number' => $newInvoice['invoice_number'],
        'sub_total' => $newInvoice['sub_total'],
        'total' => $newInvoice['total'],
        'tax' => $newInvoice['tax'],
        'discount' => $newInvoice['discount'],
        'notes' => $newInvoice['notes'],
        'customer_id' => $newInvoice['customer_id'],
        'template_name' => $newInvoice['template_name'],
    ]);
});

test('create items', function () {
    $invoice = Invoice::factory()->create();

    $items = [];

    $item = InvoiceItem::factory()->raw([
        'invoice_id' => $invoice->id,
    ]);

    array_push($items, $item);

    $request = new InvoicesRequest;

    $request->replace(['items' => $items]);

    Invoice::createItems($invoice, $request->items);

    $this->assertDatabaseHas('invoice_items', [
        'invoice_id' => $invoice->id,
        'description' => $item['description'],
        'price' => $item['price'],
        'tax' => $item['tax'],
        'quantity' => $item['quantity'],
        'total' => $item['total'],
    ]);
});

test('create taxes', function () {
    $invoice = Invoice::factory()->create();

    $taxes = [];

    $tax = Tax::factory()->raw([
        'invoice_id' => $invoice->id,
    ]);

    array_push($taxes, $tax);

    $request = new Request;

    $request->replace(['taxes' => $taxes]);

    Invoice::createTaxes($invoice, $request->taxes);

    $this->assertDatabaseHas('taxes', [
        'invoice_id' => $invoice->id,
        'name' => $tax['name'],
        'amount' => $tax['amount'],
    ]);
});
