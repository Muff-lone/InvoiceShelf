@php
    $company = $invoice->company;
    $companyIco = $company?->ico ?: $company?->tax_id;
    $isVatPayer = $company ? (method_exists($company, 'isVatPayer') ? $company->isVatPayer() : (bool) ($company->vat_payer ?? true)) : true;
@endphp
@if ($company)
    <div style="margin-top: 6px; font-size: 11px; line-height: 15px; color: #595959;">
        @if (!empty($companyIco) && !str_contains((string) ($company_address ?? ''), (string) $companyIco))
            <div><strong>@lang('pdf_ico'):</strong> {{ $companyIco }}</div>
        @endif
        @if ($isVatPayer && !empty($company->vat_id) && !str_contains((string) ($company_address ?? ''), (string) $company->vat_id))
            <div><strong>@lang('pdf_vat_id'):</strong> {{ $company->vat_id }}</div>
        @elseif (!$isVatPayer)
            <div>@lang('pdf_not_vat_payer')</div>
        @endif
        @if (!empty($company->registry_info) && !str_contains((string) ($company_address ?? ''), (string) $company->registry_info))
            <div style="margin-top: 2px;">{{ $company->registry_info }}</div>
        @endif
    </div>
@endif
