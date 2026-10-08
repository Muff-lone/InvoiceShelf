import re

with open('resources/views/app/pdf/invoice/invoice2.blade.php', 'r') as f:
    content = f.read()

# Replace the table partial reference to point to the cloned partials
content = content.replace(
    "@include('app.pdf.invoice.partials.table')",
    "@include('pdf_templates::invoice.partials.custom2.table')"
)

# Insert the customer's explicit info (IČO, VAT ID, Email, Phone) below the billing address
billing_address_html = """
            <div class="billing-address-container billing-address" @if ($shipping_address === '<br />') style="float:right; margin-right:30px;" @endif>
                @if ($billing_address)
                    <b>@lang('pdf_bill_to')</b> <br>
                    {!! $billing_address !!}
                    @if ($invoice->customer)
                        <br>
                        @if ($invoice->customer->email) {{ $invoice->customer->email }} <br> @endif
                        @if ($invoice->customer->phone) {{ $invoice->customer->phone }} <br> @endif
                        @if ($invoice->customer->ico) <b>IČO:</b> {{ $invoice->customer->ico }} <br> @endif
                    @endif
                @endif
            </div>
"""

content = re.sub(
    r'<div class="billing-address-container billing-address" .*?</div>',
    billing_address_html,
    content,
    flags=re.DOTALL
)

# Insert payment details right before items-table-wrapper
content = content.replace(
    '<div class="items-table-wrapper">',
    "@include('app.pdf.partials.payment-details')\n\n        <div class=\"items-table-wrapper\">"
)

with open('storage/app/templates/pdf/invoice/custom2.blade.php', 'w') as f:
    f.write(content)
