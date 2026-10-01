<?php

namespace App\Support;

use App\Rules\PhoneNumberRule;

/**
 * The only settings the admin screen may write. Anything not listed here is ignored,
 * so a crafted form cannot create arbitrary configuration keys.
 */
final class SettingsSchema
{
    /**
     * @return array<string, array{group: string, cast: string, label: string, input: string, rules: list<mixed>, help?: string, options?: array<string, string>, default?: mixed}>
     */
    public static function definitions(): array
    {
        return [
            'company_name' => ['group' => 'branding', 'cast' => 'string', 'label' => 'Company name', 'input' => 'text', 'rules' => ['required', 'string', 'max:120']],
            'phone' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Public phone number', 'input' => 'tel', 'rules' => ['required', 'string', new PhoneNumberRule]],
            'whatsapp' => ['group' => 'contact', 'cast' => 'string', 'label' => 'WhatsApp number', 'input' => 'tel', 'rules' => ['nullable', 'string', new PhoneNumberRule], 'help' => 'Leave blank to hide WhatsApp buttons.'],
            'email' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Public email', 'input' => 'email', 'rules' => ['required', 'email:rfc', 'max:255']],
            'address' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Office address', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            'office_hours' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Office hours', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:120']],
            'social_links' => ['group' => 'contact', 'cast' => 'json', 'label' => 'Social profile links', 'input' => 'lines', 'rules' => ['nullable', 'array', 'max:8'], 'help' => 'One https:// link per line.', 'default' => []],
            'sales_phone' => ['group' => 'leads', 'cast' => 'string', 'label' => 'Sales desk phone', 'input' => 'tel', 'rules' => ['nullable', 'string', new PhoneNumberRule], 'help' => 'Used on listings without an assigned contact. Falls back to the public phone.'],
            'sales_inbox_email' => ['group' => 'leads', 'cast' => 'string', 'label' => 'Sales inbox email', 'input' => 'email', 'rules' => ['nullable', 'email:rfc', 'max:255'], 'help' => 'Receives a copy of every new enquiry.'],
            'consent_text' => ['group' => 'leads', 'cast' => 'string', 'label' => 'Enquiry consent wording', 'input' => 'textarea', 'rules' => ['required', 'string', 'max:500']],
            'overdue_digest_enabled' => ['group' => 'leads', 'cast' => 'bool', 'label' => 'Send a morning digest of overdue follow-ups', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'lead_retention_days' => ['group' => 'leads', 'cast' => 'int', 'label' => 'Delete closed leads after (days)', 'input' => 'number', 'rules' => ['required', 'integer', 'min:30', 'max:3650'], 'default' => 730],
            'enable_sale' => ['group' => 'listings', 'cast' => 'bool', 'label' => 'Show properties for sale', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'enable_rent' => ['group' => 'listings', 'cast' => 'bool', 'label' => 'Show properties for rent', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'address_display_mode' => ['group' => 'listings', 'cast' => 'string', 'label' => 'Public address and map pin', 'input' => 'select', 'rules' => ['required', 'in:exact,approximate,hidden'], 'options' => ['exact' => 'Exact address and pin', 'approximate' => 'Area only, approximate pin', 'hidden' => 'Area only, no map'], 'default' => 'approximate'],
            'coordinate_precision' => ['group' => 'listings', 'cast' => 'int', 'label' => 'Approximate pin precision (decimal places)', 'input' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:4'], 'default' => 2, 'help' => '2 decimals is roughly 1 km; 3 is roughly 100 m.'],
        ];
    }

    public static function defaultConsentText(): string
    {
        return 'I agree that Urban Haven may contact me about this enquiry by phone, WhatsApp or email.';
    }
}
