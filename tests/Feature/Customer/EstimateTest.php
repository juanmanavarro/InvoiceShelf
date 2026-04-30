<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\Estimate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

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

test('get customer estimates', function () {
    $customer = Auth::guard('customer')->user();

    getJson("api/v1/{$customer->company->slug}/customer/estimates?page=1")->assertOk();
});

test('get customer estimates orders by estimate date descending by default', function () {
    $customer = Auth::guard('customer')->user();

    $recentEstimate = Estimate::factory()->create([
        'company_id' => $customer->company_id,
        'customer_id' => $customer->id,
        'status' => Estimate::STATUS_SENT,
        'estimate_number' => 'CEST-DATE-RECENT',
        'estimate_date' => '2024-02-01',
    ]);

    $olderEstimate = Estimate::factory()->create([
        'company_id' => $customer->company_id,
        'customer_id' => $customer->id,
        'status' => Estimate::STATUS_SENT,
        'estimate_number' => 'CEST-DATE-OLDER',
        'estimate_date' => '2024-01-01',
    ]);

    $response = getJson("api/v1/{$customer->company->slug}/customer/estimates?page=1&limit=20");

    expect(collect($response->json('data'))->pluck('id')->take(2)->all())
        ->toBe([$recentEstimate->id, $olderEstimate->id]);
});

test('get customer estimate', function () {
    $customer = Auth::guard('customer')->user();

    $estimate = Estimate::factory()->create([
        'customer_id' => $customer->id,
    ]);

    getJson("/api/v1/{$customer->company->slug}/customer/estimates/{$estimate->id}")
        ->assertOk();
});

test('customer estimate mark as accepted', function () {
    $customer = Auth::guard('customer')->user();

    $estimate = Estimate::factory()->create([
        'estimate_date' => '1988-07-18',
        'expiry_date' => '1988-08-18',
        'customer_id' => $customer->id,
    ]);

    $status = [
        'status' => Estimate::STATUS_ACCEPTED,
    ];

    $response = postJson("api/v1/{$customer->company->slug}/customer/estimate/{$estimate->id}/status", $status)
        ->assertOk();

    $this->assertEquals($response->json()['data']['status'], Estimate::STATUS_ACCEPTED);
});

test('customer estimate mark as rejected', function () {
    $customer = Auth::guard('customer')->user();

    $estimate = Estimate::factory()->create([
        'estimate_date' => '1988-07-18',
        'expiry_date' => '1988-08-18',
        'customer_id' => $customer->id,
    ]);

    $status = [
        'status' => Estimate::STATUS_REJECTED,
    ];

    $response = postJson("api/v1/{$customer->company->slug}/customer/estimate/{$estimate->id}/status", $status)
        ->assertOk();

    $this->assertEquals($response->json()['data']['status'], Estimate::STATUS_REJECTED);
});
