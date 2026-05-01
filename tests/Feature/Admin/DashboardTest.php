<?php

use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

    $user = User::find(1);
    $this->withHeaders([
        'company' => $user->companies()->first()->id,
    ]);
    Sanctum::actingAs(
        $user,
        ['*']
    );
});

getJson('api/v1/dashboard')->assertOk();

test('dashboard excludes draft invoices from summary metrics and overdue invoices', function () {
    $user = User::find(1);
    $companyId = $user->companies()->first()->id;

    $fiscalYear = CompanySetting::getSetting('fiscal_year', $companyId);
    $terms = explode('-', $fiscalYear);
    $companyStartMonth = (int) $terms[0];

    $startDate = Carbon::now();

    if ($companyStartMonth <= $startDate->month) {
        $startDate->month($companyStartMonth)->startOfMonth();
    } else {
        $startDate->subYear()->month($companyStartMonth)->startOfMonth();
    }

    $invoiceDate = $startDate->copy()->addDays(10);

    $includedInvoice = Invoice::factory()->create([
        'company_id' => $companyId,
        'status' => Invoice::STATUS_SENT,
        'paid_status' => Invoice::STATUS_UNPAID,
        'invoice_date' => $invoiceDate,
        'base_total' => 1000,
        'base_due_amount' => 400,
    ]);

    $draftInvoice = Invoice::factory()->create([
        'company_id' => $companyId,
        'status' => Invoice::STATUS_DRAFT,
        'paid_status' => Invoice::STATUS_UNPAID,
        'invoice_date' => $invoiceDate,
        'base_total' => 5000,
        'base_due_amount' => 3000,
    ]);

    $response = getJson('api/v1/dashboard')->assertOk();

    expect($response['total_invoice_count'])->toBe(1)
        ->and($response['total_amount_due'])->toBe(400)
        ->and($response['total_sales'])->toBe(1000)
        ->and(collect($response['recent_due_invoices'])->pluck('id')->all())
        ->toContain($includedInvoice->id)
        ->not->toContain($draftInvoice->id)
        ->and(collect($response['chart_data']['invoice_totals'])->sum())
        ->toBe(1000);
});

getJson('api/v1/search?name=ab')->assertOk();
