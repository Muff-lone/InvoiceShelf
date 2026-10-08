<?php

namespace App\Domains\Sales\Support;

use App\Domains\Sales\Models\Invoice;

class PaymentQrService
{
    /**
     * Determines the optimal QR code payload (Czech SPD or SEPA EPC) for the invoice, if supported.
     */
    public function generatePayload(Invoice $invoice): ?string
    {
        if ($invoice->status === 'PAID') {
            return null;
        }
        
        $company = $invoice->company;
        $currencyCode = $invoice->currency ? $invoice->currency->code : null;
        
        // Priority 1: CZK uses Czech SPD (QR Platba)
        if ($currencyCode === 'CZK') {
            if (!empty($company->bank_iban) || (!empty($company->bank_account_number) && !empty($company->bank_code))) {
                return $this->generateSpdPayload($invoice);
            }
        }

        // Priority 2: EUR uses SEPA EPC
        if ($currencyCode === 'EUR' && !empty($company->bank_iban)) {
            return $this->generateEpcPayload($invoice);
        }

        return null;
    }

    /**
     * Generate the Base64 image data for the QR code payload.
     */
    public function generateQrBase64(string $payload): ?string
    {
        try {
            $url = 'https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=' . urlencode($payload) . '&choe=UTF-8&chld=M|0';
            $image = @file_get_contents($url);
            if ($image) {
                return 'data:image/png;base64,' . base64_encode($image);
            }
        } catch (\Exception $e) {
            // Silently fail if external API is unreachable
        }
        return null;
    }

    private function generateSpdPayload(Invoice $invoice): string
    {
        $company = $invoice->company;
        $amount = number_format($invoice->due_amount / 100, 2, '.', '');
        
        $payload = "SPD*1.0*AM:{$amount}*CC:CZK";

        if (!empty($company->bank_iban)) {
            $payload .= "*IB:" . strtoupper(str_replace(' ', '', $company->bank_iban));
        } else {
            $account = $company->bank_account_number;
            if (!empty($company->bank_account_prefix)) {
                $account = $company->bank_account_prefix . '-' . $account;
            }
            $payload .= "*ACC:" . $account . '/' . $company->bank_code;
        }

        // Variable symbol is usually the invoice number (digits only)
        $vs = preg_replace('/[^0-9]/', '', $invoice->invoice_number);
        if (!empty($vs)) {
            // Truncate VS to 10 digits as per spec
            $payload .= "*X-VS:" . substr($vs, 0, 10);
        }

        $payload .= "*MSG:" . substr("Invoice " . $invoice->invoice_number, 0, 60);

        return $payload;
    }

    private function generateEpcPayload(Invoice $invoice): string
    {
        $company = $invoice->company;
        $amount = number_format($invoice->due_amount / 100, 2, '.', '');
        
        $beneficiary = $company->bank_account_holder ?: $company->name;
        // Limit beneficiary to 70 chars
        $beneficiary = mb_substr($beneficiary, 0, 70);
        
        $iban = strtoupper(str_replace(' ', '', $company->bank_iban));
        $bic = strtoupper(str_replace(' ', '', $company->bank_bic ?? ''));

        // EPC Barcode format
        // 1: BCD
        // 2: 002 (Version)
        // 3: 1 (Charset 1=UTF-8)
        // 4: SCT (SEPA Credit Transfer)
        // 5: BIC (optional)
        // 6: Beneficiary name
        // 7: IBAN
        // 8: Amount
        // 9: Purpose (optional)
        // 10: Remittance Information (Structured or Unstructured)
        // 11: Remittance Information
        
        $lines = [
            'BCD',
            '002',
            '1',
            'SCT',
            $bic,
            $beneficiary,
            $iban,
            'EUR' . $amount,
            '', // Purpose
            '', // Structured Remittance
            'Invoice ' . $invoice->invoice_number, // Unstructured Remittance
            '' // Beneficiary to originator info
        ];

        return implode("\n", $lines);
    }
}
