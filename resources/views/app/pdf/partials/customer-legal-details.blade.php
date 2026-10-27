@php
    $customer = $invoice->customer;
@endphp
@if ($customer && (!empty($customer->ico) || !empty($customer->tax_id)))
    <div style="margin-top: 6px; font-size: 11px; line-height: 15px; color: #595959;">
        @if (!empty($customer->ico) && !str_contains((string) ($billing_address ?? ''), (string) $customer->ico))
            <div><strong>@lang('pdf_ico'):</strong> {{ $customer->ico }}</div>
        @endif
        @if (!empty($customer->tax_id) && !str_contains((string) ($billing_address ?? ''), (string) $customer->tax_id))
            <div><strong>@lang('pdf_vat_id'):</strong> {{ $customer->tax_id }}</div>
        @endif
    </div>
@endif
