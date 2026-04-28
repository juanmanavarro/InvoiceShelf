<?php

test('invoice and estimate pdf templates do not render the billing address label', function () {
    $templates = [
        'resources/views/app/pdf/invoice/invoice1.blade.php',
        'resources/views/app/pdf/invoice/invoice2.blade.php',
        'resources/views/app/pdf/invoice/invoice3.blade.php',
        'resources/views/app/pdf/estimate/estimate1.blade.php',
        'resources/views/app/pdf/estimate/estimate2.blade.php',
        'resources/views/app/pdf/estimate/estimate3.blade.php',
    ];

    foreach ($templates as $template) {
        expect(file_get_contents(base_path($template)))
            ->not->toContain('pdf_bill_to')
            ->toContain('$billing_address');
    }
});

test('invoice pdf templates render the bank transfer footer', function () {
    $templates = [
        'resources/views/app/pdf/invoice/invoice1.blade.php',
        'resources/views/app/pdf/invoice/invoice2.blade.php',
        'resources/views/app/pdf/invoice/invoice3.blade.php',
    ];

    foreach ($templates as $template) {
        expect(file_get_contents(base_path($template)))
            ->toContain("include('app.pdf.invoice.partials.footer')");
    }

    expect(file_get_contents(base_path('resources/views/app/pdf/invoice/partials/footer.blade.php')))
        ->toContain('position: fixed')
        ->toContain('bottom: 32px')
        ->toContain('text-align: center')
        ->toContain('<strong>ES63 0073 0100 5304 6040 5315</strong>');
});

test('invoice pdf templates render black invoice title instead of company name fallback', function () {
    $templates = [
        'resources/views/app/pdf/invoice/invoice1.blade.php',
        'resources/views/app/pdf/invoice/invoice2.blade.php',
        'resources/views/app/pdf/invoice/invoice3.blade.php',
    ];

    foreach ($templates as $template) {
        expect(file_get_contents(base_path($template)))
            ->toContain('color: #040405')
            ->toContain('Factura')
            ->not->toContain('$invoice->customer->company->name');
    }
});

test('invoice pdf table renders custom task and total headings', function () {
    expect(file_get_contents(base_path('resources/views/app/pdf/invoice/partials/table.blade.php')))
        ->toContain('Tareas')
        ->toContain('Total')
        ->not->toContain("lang('pdf_items_label')")
        ->not->toContain("lang('pdf_amount_label')");
});
