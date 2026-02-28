<x-mail::message>
# Invoice {{ $invoiceNumber }}

Dear {{ $clientName }},

Please find attached your invoice from **{{ $tenantName }}**.

**Invoice Details:**
- Invoice Number: {{ $invoiceNumber }}
- Amount Due: ${{ $total }}
- Due Date: {{ $dueDate }}

<x-mail::button :url="$viewUrl">
View Invoice Online
</x-mail::button>

If you have any questions about this invoice, please contact us.

Thanks,<br>
{{ $tenantName }}
</x-mail::message>
