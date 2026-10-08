@php
    $qrService = new \App\Domains\Sales\Support\PaymentQrService();
    $qrPayload = $qrService->generatePayload($invoice);
    $qrImage = $qrPayload ? $qrService->generateQrBase64($qrPayload) : null;
    $company = $invoice->company;
@endphp

@if ($company->bank_name || $company->bank_iban || $company->bank_account_number)
<div style="margin-top: 20px; page-break-inside: avoid; clear: both; overflow: hidden; padding-left: 30px; padding-right: 30px;">
    <div style="float: left; width: 60%; font-size: 12px; color: #595959;">
        <div style="font-size: 15px; font-weight: bold; color: #040405; margin-bottom: 8px;">
            @lang('pdf_payment_details')
        </div>
        
        @if ($company->bank_name)
            <div><strong>Bank:</strong> {{ $company->bank_name }}</div>
        @endif
        
        @if ($company->bank_account_holder)
            <div><strong>Beneficiary:</strong> {{ $company->bank_account_holder }}</div>
        @endif
        
        @if ($company->bank_iban)
            <div><strong>IBAN:</strong> {{ $company->bank_iban }}</div>
        @endif
        
        @if ($company->bank_bic)
            <div><strong>BIC / SWIFT:</strong> {{ $company->bank_bic }}</div>
        @endif
        
        @if ($company->bank_account_number)
            <div>
                <strong>Account No:</strong> 
                {{ $company->bank_account_prefix ? $company->bank_account_prefix . '-' : '' }}{{ $company->bank_account_number }}{{ $company->bank_code ? '/' . $company->bank_code : '' }}
            </div>
        @endif
    </div>

    @if ($qrImage)
    <div style="float: right; width: 35%; text-align: right;">
        <img src="{{ $qrImage }}" style="width: 100px; height: 100px; margin-top: 5px;" alt="Payment QR Code">
        <div style="font-size: 9px; color: #888; margin-top: 3px;">Scan to pay</div>
    </div>
    @endif
    
    <div style="clear: both;"></div>
</div>
@endif
