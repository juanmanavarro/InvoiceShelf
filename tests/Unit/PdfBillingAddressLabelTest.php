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
