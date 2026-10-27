<!DOCTYPE html>
<html>

<head>
    <title>@lang((method_exists($invoice, 'isCreditNote') && $invoice->isCreditNote()) ? 'pdf_credit_note_label' : 'pdf_invoice_label') - {{ $invoice->invoice_number }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
@if (view()->exists("app.pdf.partials.fonts"))
    @include("app.pdf.partials.fonts")
@else
    <style type="text/css">
        body { font-family: 'DejaVu Sans', sans-serif; }
    </style>
@endif

    <style type="text/css">
        @page {
            margin: 0px;
        }

        body {
            margin: 0px;
            padding: 0px;
            font-size: 13px;
            color: #111111;
            line-height: 1.4;
        }

        table {
            border-collapse: collapse;
        }

        /* -- Header -- */
        .header-table {
            width: 100%;
            margin-bottom: 0px;
        }

        .header-logo-cell {
            padding: 25px 25px 0 35px;
            vertical-align: top;
        }

        .header-title-cell {
            background: #0074D9;
            color: #FFFFFF;
            padding: 16px 30px;
            vertical-align: top;
            text-align: left;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            line-height: 28px;
            color: #FFFFFF;
        }

        .invoice-subtitle {
            font-size: 13px;
            margin-top: 3px;
            color: #FFFFFF;
            opacity: 0.95;
        }

        /* -- Address Section -- */
        .address-table {
            width: 100%;
            margin-top: 20px;
        }

        .supplier-cell {
            padding-left: 35px;
            padding-right: 25px;
            border-right: 1px solid #E4E5E4;
            vertical-align: top;
        }

        .customer-cell {
            padding-left: 25px;
            padding-right: 35px;
            vertical-align: top;
        }

        .section-label {
            font-size: 11px;
            font-weight: bold;
            color: #5E5E5E;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .party-name {
            font-size: 15px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 3px;
        }

        .party-address {
            font-size: 13px;
            line-height: 19px;
            color: #111111;
        }

        .id-table {
            width: 100%;
            margin-top: 15px;
        }

        .id-label {
            font-size: 13px;
            color: #5E5E5E;
            width: 50px;
            padding: 2px 0;
            text-align: left;
        }

        .id-value {
            font-size: 13px;
            color: #000000;
            padding: 2px 0;
            text-align: right;
        }

        /* -- Middle Boxes -- */
        .middle-boxes-table {
            width: 100%;
            margin-top: 25px;
        }

        .payment-box {
            background: #0074D9;
            color: #FFFFFF;
            padding: 14px 25px 14px 35px;
            vertical-align: top;
        }

        .dates-box {
            background: #F1F1F1;
            color: #111111;
            padding: 14px 35px 14px 25px;
            vertical-align: top;
        }

        .box-label-white {
            font-size: 13px;
            color: #FFFFFF;
            padding: 3px 0;
            text-align: left;
        }

        .box-value-white-bold {
            font-size: 13px;
            font-weight: bold;
            color: #FFFFFF;
            padding: 3px 0;
            text-align: right;
        }

        .box-value-white {
            font-size: 13px;
            color: #FFFFFF;
            padding: 3px 0;
            text-align: right;
        }

        .box-label-gray {
            font-size: 13px;
            color: #5E5E5E;
            padding: 3px 0;
            text-align: left;
        }

        .box-value-gray-bold {
            font-size: 13px;
            font-weight: bold;
            color: #111111;
            padding: 3px 0;
            text-align: right;
        }

        .box-value-gray {
            font-size: 13px;
            color: #111111;
            padding: 3px 0;
            text-align: right;
        }

        /* -- Intro Line -- */
        .intro-heading {
            padding: 25px 35px 10px 35px;
            font-size: 13px;
            color: #5E5E5E;
        }

        /* -- Items Table -- */
        .items-table-wrapper {
            width: 100%;
        }

        .items-table-inset {
            padding-left: 35px;
            padding-right: 35px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        tr.item-table-heading-row th {
            border-bottom: 1px solid #CCCCCC;
            font-size: 11px;
            font-weight: bold;
            color: #5E5E5E;
            text-transform: uppercase;
            padding: 8px 10px;
            line-height: 16px;
        }

        tr.item-row td {
            border-bottom: 1px solid #E8E8E8;
            font-size: 13px;
            line-height: 18px;
            padding: 10px;
        }

        .item-name {
            font-size: 13px;
            color: #000000;
            display: block;
        }

        .item-description {
            color: #5E5E5E;
            font-size: 11px;
            line-height: 14px;
            display: block;
            margin-top: 3px;
        }

        .item-cell-total {
            background: #F1F1F1 !important;
            font-weight: 500;
        }

        /* -- Bottom Section -- */
        .bottom-section {
            width: 100%;
            page-break-inside: avoid;
        }

        .qr-code-box {
            border: 1px solid #CCCCCC;
            padding: 6px;
            width: 130px;
            text-align: center;
            background: #FFFFFF;
        }

        .qr-code-label {
            font-size: 10px;
            color: #777777;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
            letter-spacing: 0.5px;
        }

        .notes-container {
            margin-top: 15px;
            max-width: 320px;
            font-size: 11px;
            color: #5E5E5E;
        }

        .notes-label {
            font-weight: bold;
            margin-bottom: 3px;
            color: #111111;
        }

        .notes-body {
            line-height: 16px;
        }

        .tax-summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tax-head-row th {
            font-size: 11px;
            font-weight: bold;
            color: #5E5E5E;
            text-transform: uppercase;
            padding: 6px 10px;
        }

        .tax-data-row td {
            font-size: 13px;
            color: #111111;
            padding: 6px 10px;
        }

        .tax-accent-line {
            height: 2px;
            background: #0074D9;
            margin-top: 2px;
            margin-bottom: 10px;
        }

        .grand-total-amount {
            text-align: right;
            font-size: 26px;
            font-weight: bold;
            color: #0074D9;
            padding-right: 10px;
            margin-top: 6px;
        }

        .payment-summary-block {
            text-align: right;
            font-size: 12px;
            color: #5E5E5E;
            margin-top: 8px;
            padding-right: 10px;
        }

        .amount-due-line {
            font-weight: bold;
            color: #0074D9;
            margin-top: 3px;
        }

        .signature-block {
            text-align: right;
            margin-top: 35px;
            padding-right: 10px;
        }

        .signature-line {
            display: inline-block;
            width: 170px;
            border-top: 1px solid #CCCCCC;
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
            color: #888888;
        }

        /* Helpers */
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>

<body>
    @if (view()->exists('app.pdf.partials.credit-note-banner'))
        @include('app.pdf.partials.credit-note-banner')
    @endif

    @php
        $isVatPayer = $invoice->company ? (method_exists($invoice->company, 'isVatPayer') ? $invoice->company->isVatPayer() : (bool) ($invoice->company->vat_payer ?? true)) : true;
        $isCreditNote = method_exists($invoice, 'isCreditNote') && $invoice->isCreditNote();
        $logoSrc = null;
        if ($logo) {
            if (class_exists('\App\Platform\Pdf\Rendering\ImageUtils')) {
                $logoSrc = \App\Platform\Pdf\Rendering\ImageUtils::toBase64Src($logo);
            } elseif (class_exists('\App\Support\Pdf\ImageUtils')) {
                $logoSrc = \App\Support\Pdf\ImageUtils::toBase64Src($logo);
            } elseif (file_exists($logo)) {
                $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logo));
            } else {
                $logoSrc = $logo;
            }
        }
    @endphp

    {{-- Top Header: Logo on left, Blue Title box on right --}}
    <table cellspacing="0" cellpadding="0" class="header-table">
        <tr>
            <td width="50%" class="header-logo-cell">
                @if ($logoSrc)
                    <img style="max-height: 48px; max-width: 240px;" src="{{ $logoSrc }}" alt="Company Logo">
                @elseif ($invoice->company)
                    <div style="font-size: 20px; font-weight: bold; color: #0074D9; text-transform: uppercase;">
                        {{ $invoice->company->name }}
                    </div>
                @endif
            </td>
            <td width="50%" class="header-title-cell">
                <div class="invoice-title">
                    @lang($isCreditNote ? 'pdf_credit_note_label' : 'pdf_invoice_label') {{ $invoice->invoice_number }}
                </div>
                @if ($isCreditNote || $isVatPayer)
                    <div class="invoice-subtitle">
                        {{ $isCreditNote ? ($isVatPayer ? 'Opravný daňový doklad' : __('pdf_credit_note_label')) : 'Daňový doklad' }}
                    </div>
                @endif
                @if (view()->exists('app.pdf.partials.document-custom-fields-stacked'))
                    @include('app.pdf.partials.document-custom-fields-stacked', ['document' => $invoice])
                @endif
            </td>
        </tr>
    </table>

    {{-- Addresses: Dodavatel (Supplier) on left, Odběratel (Customer) on right with vertical divider --}}
    <table cellspacing="0" cellpadding="0" class="address-table">
        <tr>
            <td width="50%" class="supplier-cell">
                <div class="section-label">DODAVATEL</div>
                <div class="party-name">{{ $invoice->company?->name ?? '' }}</div>
                <div class="party-address">
                    @if ($invoice->company && $invoice->company->address)
                        {{ $invoice->company->address->address_street_1 }}<br>
                        @if ($invoice->company->address->address_street_2)
                            {{ $invoice->company->address->address_street_2 }}<br>
                        @endif
                        {{ $invoice->company->address->zip }} {{ $invoice->company->address->city }}
                        @if ($invoice->company->address->country_name)
                            <br>{{ $invoice->company->address->country_name }}
                        @endif
                    @elseif ($company_address)
                        {!! $company_address !!}
                    @endif
                </div>

                @php
                    $companyIco = $invoice->company?->ico ?: $invoice->company?->tax_id;
                @endphp
                <table class="id-table">
                    @if (!empty($companyIco))
                        <tr>
                            <td class="id-label">IČO</td>
                            <td class="id-value">{{ $companyIco }}</td>
                        </tr>
                    @endif
                    @if ($isVatPayer && !empty($invoice->company?->vat_id))
                        <tr>
                            <td class="id-label">DIČ</td>
                            <td class="id-value">{{ $invoice->company->vat_id }}</td>
                        </tr>
                    @endif
                </table>
                @if (!$isVatPayer)
                    <div style="font-size: 11px; color: #5E5E5E; margin-top: 6px;">@lang('pdf_not_vat_payer')</div>
                @endif
                @if (!empty($invoice->company?->registry_info))
                    <div style="font-size: 11px; color: #5E5E5E; margin-top: 6px;">{{ $invoice->company->registry_info }}</div>
                @endif
            </td>

            <td width="50%" class="customer-cell">
                <div class="section-label">ODBĚRATEL</div>
                <div class="party-name">{{ $invoice->customer?->name ?? '' }}</div>
                <div class="party-address">
                    @if ($invoice->customer && $invoice->customer->billingAddress)
                        {{ $invoice->customer->billingAddress->address_street_1 }}<br>
                        @if ($invoice->customer->billingAddress->address_street_2)
                            {{ $invoice->customer->billingAddress->address_street_2 }}<br>
                        @endif
                        {{ $invoice->customer->billingAddress->zip }} {{ $invoice->customer->billingAddress->city }}
                        @if ($invoice->customer->billingAddress->country_name)
                            <br>{{ $invoice->customer->billingAddress->country_name }}
                        @endif
                    @elseif ($billing_address)
                        {!! $billing_address !!}
                    @endif
                </div>

                <table class="id-table">
                    @if (!empty($invoice->customer?->ico))
                        <tr>
                            <td class="id-label">IČO</td>
                            <td class="id-value">{{ $invoice->customer->ico }}</td>
                        </tr>
                    @endif
                    @if (!empty($invoice->customer?->tax_id))
                        <tr>
                            <td class="id-label">DIČ</td>
                            <td class="id-value">{{ $invoice->customer->tax_id }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Middle Infoboxes: Payment details on left (Blue), Dates on right (Gray) --}}
    <table cellspacing="0" cellpadding="0" class="middle-boxes-table">
        <tr>
            <td width="50%" class="payment-box">
                @php
                    $bankAccount = $invoice->company?->bank_account_number ?? null;
                    if (!empty($invoice->company?->bank_account_prefix)) {
                        $bankAccount = $invoice->company->bank_account_prefix . '-' . $bankAccount;
                    }
                    if (!empty($invoice->company?->bank_code)) {
                        $bankAccount .= '/' . $invoice->company->bank_code;
                    }
                    if (empty($bankAccount) && !empty($invoice->company?->bank_iban)) {
                        $bankAccount = $invoice->company->bank_iban;
                    }
                    $variableSymbol = !empty($invoice->variable_symbol)
                        ? $invoice->variable_symbol
                        : preg_replace('/[^0-9]/', '', (string) $invoice->invoice_number);
                    $paymentMethodLabel = $invoice->formattedPaymentMethod ?? ($invoice->payment_method ?: 'Převodem');
                @endphp
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="box-label-white">Bankovní účet</td>
                        <td class="box-value-white-bold">{{ $bankAccount ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="box-label-white">Variabilní symbol</td>
                        <td class="box-value-white-bold">{{ $variableSymbol ?: $invoice->invoice_number }}</td>
                    </tr>
                    @if (!empty($invoice->constant_symbol))
                        <tr>
                            <td class="box-label-white">Konstantní symbol</td>
                            <td class="box-value-white">{{ $invoice->constant_symbol }}</td>
                        </tr>
                    @endif
                    @if (!empty($invoice->specific_symbol))
                        <tr>
                            <td class="box-label-white">Specifický symbol</td>
                            <td class="box-value-white">{{ $invoice->specific_symbol }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="box-label-white">Způsob platby</td>
                        <td class="box-value-white">{{ $paymentMethodLabel }}</td>
                    </tr>
                    @if (!empty($invoice->company?->bank_iban) && $bankAccount !== $invoice->company->bank_iban)
                        <tr>
                            <td class="box-label-white" style="font-size: 11px; opacity: 0.85;">IBAN</td>
                            <td class="box-value-white" style="font-size: 11px; opacity: 0.85;">{{ $invoice->company->bank_iban }}</td>
                        </tr>
                    @endif
                    @if (!empty($invoice->company?->bank_bic))
                        <tr>
                            <td class="box-label-white" style="font-size: 11px; opacity: 0.85;">BIC / SWIFT</td>
                            <td class="box-value-white" style="font-size: 11px; opacity: 0.85;">{{ $invoice->company->bank_bic }}</td>
                        </tr>
                    @endif
                </table>
            </td>
            <td width="50%" class="dates-box">
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="box-label-gray">Datum vystavení</td>
                        <td class="box-value-gray">{{ $invoice->formattedInvoiceDate }}</td>
                    </tr>
                    <tr>
                        <td class="box-label-gray">Datum splatnosti</td>
                        <td class="box-value-gray-bold">{{ $invoice->formattedDueDate }}</td>
                    </tr>
                    @if ($isVatPayer || !empty($invoice->taxable_supply_date))
                        <tr>
                            <td class="box-label-gray">Datum zdan. plnění</td>
                            <td class="box-value-gray">{{ $invoice->formattedTaxableSupplyDate ?: $invoice->formattedInvoiceDate }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Intro heading before table --}}
    <div class="intro-heading">
        Fakturujeme Vám následující položky
    </div>

    {{-- Items Table & Bottom Totals / QR Code --}}
    <div class="items-table-wrapper">
        @if (view()->exists('pdf_templates::invoice.partials.custom2.table'))
            @include('pdf_templates::invoice.partials.custom2.table')
        @elseif (view()->exists('app.pdf.invoice.partials.table'))
            @include('app.pdf.invoice.partials.table')
        @endif
    </div>
</body>

</html>
