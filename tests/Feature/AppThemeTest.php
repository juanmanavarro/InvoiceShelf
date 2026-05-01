<?php

test('app view includes theme bootstrap for dark mode support', function () {
    $html = view('app')->render();

    expect($html)
        ->toContain('class="h-full"')
        ->toContain('dark:bg-gray-950')
        ->toContain('window.InvoiceShelf.start()');
});
