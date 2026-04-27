@php
    $visibleCustomFields = $model->fields->filter(function ($field) {
        return $field->customField && $field->defaultAnswer !== null && $field->defaultAnswer !== '';
    });
@endphp

@if ($visibleCustomFields->isNotEmpty() && ($asRows ?? false))
    @foreach ($visibleCustomFields as $field)
        <tr>
            <td class="attribute-label">{{ $field->customField->label }}</td>
            <td class="attribute-value"> &nbsp;{!! nl2br(e($field->defaultAnswer)) !!}</td>
        </tr>
    @endforeach
@elseif ($visibleCustomFields->isNotEmpty())
    <div style="{{ $containerStyle ?? 'clear: both; margin: 20px 30px 0; page-break-inside: avoid;' }}">
        <table width="100%" cellspacing="0" border="0">
            @foreach ($visibleCustomFields as $field)
                <tr>
                    <td style="width: 35%; padding: 4px 10px 4px 0; font-size: 12px; line-height: 18px; color: #55547A; vertical-align: top;">
                        {{ $field->customField->label }}
                    </td>
                    <td style="padding: 4px 0; font-size: 12px; line-height: 18px; color: #040405; vertical-align: top;">
                        {!! nl2br(e($field->defaultAnswer)) !!}
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endif
