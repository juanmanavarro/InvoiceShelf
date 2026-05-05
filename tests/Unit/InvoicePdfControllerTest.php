<?php

use App\Http\Controllers\V1\PDF\InvoicePdfController;
use App\Models\Invoice;
use Illuminate\Http\Request;

afterEach(function () {
    Mockery::close();
});

test('invoice pdf route can force a download response', function () {
    $invoice = Mockery::mock(Invoice::class)->makePartial();
    $invoice
        ->shouldReceive('getGeneratedPDFOrStream')
        ->once()
        ->with('invoice', true)
        ->andReturn(response('pdf'));

    $response = (new InvoicePdfController)(new Request(['download' => '1']), $invoice);

    expect($response->getContent())->toBe('pdf');
});
