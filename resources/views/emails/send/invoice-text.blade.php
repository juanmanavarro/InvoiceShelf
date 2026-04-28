@php
    $body = preg_replace('/<\s*br\s*\/?\s*>|<\s*\/br\s*>/i', "\n\n", $data['body']);
    $body = preg_replace('/<\s*\/p\s*>|<\s*\/div\s*>/i', "\n\n", $body);
    $body = preg_replace('/<\s*p[^>]*>|<\s*div[^>]*>/i', '', $body);
    $body = html_entity_decode(strip_tags($body), ENT_QUOTES, 'UTF-8');
    $body = preg_replace("/[ \t]+\n/", "\n", $body);
    $body = preg_replace("/\n{3,}/", "\n\n", $body);
@endphp

{{ trim($body) }}

@if (! $data['attach']['data'])
{{ __('mail_view_invoice') }}: {{ $data['url'] }}
@endif
