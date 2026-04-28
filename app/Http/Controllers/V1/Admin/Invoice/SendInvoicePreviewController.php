<?php

namespace App\Http\Controllers\V1\Admin\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendInvoiceRequest;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendInvoicePreviewController extends Controller
{
    /**
     * Mail a specific invoice to the corresponding customer's email address.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function __invoke(SendInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('send invoice', $invoice);

        $data = $invoice->sendInvoiceData($request->all());
        $data['url'] = $invoice->invoicePdfUrl;

        return response(
            view('emails.send.invoice-text', ['data' => $data])->render(),
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }
}
