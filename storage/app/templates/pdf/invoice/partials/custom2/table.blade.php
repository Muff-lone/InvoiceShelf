<div class="items-table-inset">
    <table width="100%" class="items-table" cellspacing="0" cellpadding="0" border="0">
        <thead>
            <tr class="item-table-heading-row">
                <th width="14%" class="item-table-heading text-left">Množství</th>
                <th class="item-table-heading text-left">Položka</th>
                @foreach($customFields as $field)
                    <th class="item-table-heading text-right">{{ $field->label }}</th>
                @endforeach
                <th width="12%" class="item-table-heading text-right">DPH</th>
                <th width="18%" class="item-table-heading text-right">CENA ZA MJ</th>
                @if($invoice->discount_per_item === 'YES')
                    <th width="12%" class="item-table-heading text-right">@lang('pdf_discount_label')</th>
                @endif
                <th width="22%" class="item-table-heading text-right col-total-heading">CELKEM BEZ DPH</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                @php
                    $taxPercent = null;
                    if ($item->taxes && $item->taxes->isNotEmpty()) {
                        $taxPercent = $item->taxes->first()->percent;
                    } elseif ($invoice->taxes && $invoice->taxes->isNotEmpty()) {
                        $taxPercent = $invoice->taxes->first()->percent;
                    }
                @endphp
                <tr class="item-row">
                    <td class="item-cell text-left" style="vertical-align: top;">
                        {{ $item->quantity }} @if($item->unit_name){{ $item->unit_name }}@endif
                    </td>
                    <td class="item-cell text-left" style="vertical-align: top;">
                        <span class="item-name">{{ $item->name }}</span>
                        @if ($item->description)
                            <span class="item-description">{!! nl2br(htmlspecialchars($item->description)) !!}</span>
                        @endif
                    </td>
                    @foreach($customFields as $field)
                        <td class="item-cell text-right" style="vertical-align: top;">
                            {{ $item->getFormattedCustomFieldValueBySlug($field->slug) }}
                        </td>
                    @endforeach
                    <td class="item-cell text-right" style="vertical-align: top;">
                        {{ $taxPercent !== null ? $taxPercent . ' %' : '-' }}
                    </td>
                    <td class="item-cell text-right" style="vertical-align: top;">
                        {!! format_money_pdf($item->price, $invoice->customer->currency) !!}
                    </td>
                    @if($invoice->discount_per_item === 'YES')
                        <td class="item-cell text-right" style="vertical-align: top;">
                            @if($item->discount_type === 'fixed')
                                {!! format_money_pdf($item->discount_val, $invoice->customer->currency) !!}
                            @endif
                            @if($item->discount_type === 'percentage')
                                {{$item->discount}}%
                            @endif
                        </td>
                    @endif
                    <td class="item-cell text-right item-cell-total" style="vertical-align: top;">
                        {!! format_money_pdf($item->price * $item->quantity, $invoice->customer->currency) !!}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@php
    $qrService = new \App\Domains\Sales\Support\PaymentQrService();
    $qrPayload = $qrService->generatePayload($invoice);
    $qrImage = $qrPayload ? $qrService->generateQrBase64($qrPayload) : null;

    $taxRows = [];
    if ($invoice->tax_per_item === 'YES') {
        foreach ($invoice->items as $item) {
            $itemTax = $item->taxes->first();
            $rateKey = $itemTax ? (string)$itemTax->percent : '0';
            $rateLabel = $itemTax ? $itemTax->percent . ' %' : '0 %';
            if (!isset($taxRows[$rateKey])) {
                $taxRows[$rateKey] = [
                    'rate' => $rateLabel,
                    'base' => 0,
                    'tax' => 0,
                ];
            }
            $taxRows[$rateKey]['base'] += ($item->price * $item->quantity);
            $taxRows[$rateKey]['tax'] += $itemTax ? $itemTax->amount : 0;
        }
    } elseif ($invoice->taxes && $invoice->taxes->isNotEmpty()) {
        foreach ($invoice->taxes as $tax) {
            $rateKey = (string)$tax->percent;
            $taxRows[$rateKey] = [
                'rate' => $tax->percent . ' %',
                'base' => $invoice->sub_total - ($invoice->discount_val ?? 0),
                'tax' => $tax->amount,
            ];
        }
    } else {
        $taxRows['0'] = [
            'rate' => '0 %',
            'base' => $invoice->sub_total,
            'tax' => 0,
        ];
    }

    $creditedTotal = $invoice->relationLoaded('creditNotes') ? -(int) $invoice->creditNotes->sum('total') : 0;
    $amountPaid = (int) $invoice->total - (int) $invoice->due_amount - $creditedTotal;
@endphp

<div class="bottom-section">
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 30px;">
        <tr>
            <td width="45%" valign="top" style="padding-left: 35px; padding-right: 20px;">
                @if ($qrImage)
                    <div class="qr-code-box">
                        <img src="{{ $qrImage }}" style="width: 120px; height: 120px; display: block; margin: 0 auto;" alt="QR Platba">
                        <div class="qr-code-label">QR Platba</div>
                    </div>
                @endif

                @if ($notes)
                    <div class="notes-container">
                        <div class="notes-label">@lang('pdf_notes')</div>
                        <div class="notes-body">{!! $notes !!}</div>
                    </div>
                @endif
            </td>

            <td width="55%" valign="top" style="padding-right: 35px; padding-left: 10px;">
                <table width="100%" cellspacing="0" cellpadding="0" class="tax-summary-table">
                    <thead>
                        <tr class="tax-head-row">
                            <th class="tax-head text-left">SAZBA</th>
                            <th class="tax-head text-right">ZÁKLAD</th>
                            <th class="tax-head text-right">DPH</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($taxRows as $row)
                            <tr class="tax-data-row">
                                <td class="tax-cell text-left">{{ $row['rate'] }}</td>
                                <td class="tax-cell text-right">{!! format_money_pdf($row['base'], $invoice->customer->currency) !!}</td>
                                <td class="tax-cell text-right">{!! format_money_pdf($row['tax'], $invoice->customer->currency) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="tax-accent-line"></div>

                <div class="grand-total-amount">
                    {!! format_money_pdf($invoice->total, $invoice->customer->currency) !!}
                </div>

                @if($creditedTotal > 0 || $amountPaid > 0)
                    <div class="payment-summary-block">
                        @if($amountPaid > 0)
                            <div>@lang('pdf_amount_paid'): {!! format_money_pdf($amountPaid, $invoice->customer->currency) !!}</div>
                        @endif
                        @if($creditedTotal > 0)
                            <div>@lang('pdf_amount_credited'): {!! format_money_pdf($creditedTotal, $invoice->customer->currency) !!}</div>
                        @endif
                        <div class="amount-due-line">
                            @lang('pdf_amount_due'): {!! format_money_pdf($invoice->due_amount, $invoice->customer->currency) !!}
                        </div>
                    </div>
                @endif

                <div class="signature-block">
                    <div class="signature-line">
                        Razítko a podpis
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
