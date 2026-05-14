<?php

namespace App\Http\Controllers\V1\Admin\Estimate;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendEstimatesRequest;
use App\Models\Estimate;
use Illuminate\Http\JsonResponse;

class SendEstimatePreviewController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return JsonResponse
     */
    public function __invoke(SendEstimatesRequest $request, Estimate $estimate)
    {
        $this->authorize('send estimate', $estimate);

        $data = $estimate->sendEstimateData($request->all());
        $data['url'] = $estimate->estimatePdfUrl;

        return response(
            view('emails.send.estimate-text', ['data' => $data])->render(),
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }
}
