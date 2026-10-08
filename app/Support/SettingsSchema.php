<?php

namespace App\Support;

use App\Rules\PhoneNumberRule;

/**
 * The only settings the admin screens may write. Anything not listed here is ignored,
 * so a crafted form cannot create arbitrary configuration keys.
 */
final class SettingsSchema
{
    public const HEX_COLOUR = 'regex:/^#[0-9a-fA-F]{6}$/';

    /**
     * Admin screens and the setting groups each one edits.
     *
     * @var array<string, array{title: string, description: string, groups: list<string>}>
     */
    public const SCREENS = [
        'general' => ['title' => 'Branding', 'description' => 'Company name, tagline, logos and the icon browsers show in the tab.', 'groups' => ['branding', 'brand_assets', 'admin_login']],
        'theme' => ['title' => 'Theme & colours', 'description' => 'Set the website palette using a 60/30/10 balance: dominant surfaces, secondary structure and one accent.', 'groups' => ['theme']],
        'contact' => ['title' => 'Contact & social', 'description' => 'Public phone, email and address, the header button and footer text, social profiles and the floating chat button.', 'groups' => ['contact', 'chrome', 'floating']],
        'enquiries' => ['title' => 'Enquiries & email', 'description' => 'Where enquiries are sent, consent wording, reminders and how long closed leads are kept.', 'groups' => ['leads']],
        'listings' => ['title' => 'Listing display', 'description' => 'What the public website shows and how property addresses appear on listing pages.', 'groups' => ['listings']],
        'analytics' => ['title' => 'Analytics & tracking', 'description' => 'Google Tag Manager, Google Analytics, Google Ads and Meta Pixel, and the cookie banner that asks visitors first.', 'groups' => ['analytics', 'consent']],
        'seo' => ['title' => 'SEO defaults', 'description' => 'Default title and description, Search Console verification and whether search engines may index the site.', 'groups' => ['seo']],
        'copy' => ['title' => 'Website copy', 'description' => 'Override public website wording used across pages, controls and messages.', 'groups' => ['website_copy']],
    ];

    /**
     * @var array<string, string>
     */
    public const GROUP_LABELS = [
        'branding' => 'Identity',
        'brand_assets' => 'Logos and icons',
        'admin_login' => 'Staff sign-in page',
        'chrome' => 'Header button and footer',
        'consent' => 'Cookie banner',
        'theme' => 'Brand colours',
        'contact' => 'Contact details',
        'floating' => 'Floating chat button',
        'leads' => 'Enquiries and follow-up',
        'listings' => 'How listings appear',
        'analytics' => 'Tracking IDs',
        'seo' => 'Search engine defaults',
        'website_copy' => 'Public website phrases',
    ];

    /**
     * Image settings and the accepted upload rules for each.
     *
     * @var array<string, list<string>>
     */
    public const IMAGE_RULES = [
        'brand_logo' => ['image', 'mimes:png,webp,jpg,jpeg', 'max:2048', 'dimensions:min_width=80,min_height=20'],
        'brand_logo_dark' => ['image', 'mimes:png,webp,jpg,jpeg', 'max:2048', 'dimensions:min_width=80,min_height=20'],
        'brand_favicon' => ['image', 'mimes:png,webp', 'max:512', 'dimensions:min_width=32,min_height=32,max_width=1024,max_height=1024'],
        'brand_og_image' => ['image', 'mimes:png,webp,jpg,jpeg', 'max:4096', 'dimensions:min_width=600,min_height=315'],
        'brand_logo_mobile' => ['image', 'mimes:png,webp,jpg,jpeg', 'max:2048', 'dimensions:min_width=40,min_height=20'],
        'brand_logo_footer' => ['image', 'mimes:png,webp,jpg,jpeg', 'max:2048', 'dimensions:min_width=80,min_height=20'],
        'admin_login_image' => ['image', 'mimes:png,webp,jpg,jpeg', 'max:4096', 'dimensions:min_width=600,min_height=400'],
    ];

    /**
     * @return array<string, array{group: string, cast: string, label: string, input: string, rules: list<mixed>, help?: string, options?: array<string, string>, default?: mixed, placeholder?: string}>
     */
    public static function definitions(): array
    {
        return [
            'company_name' => ['group' => 'branding', 'cast' => 'string', 'label' => 'Company name', 'input' => 'text', 'rules' => ['required', 'string', 'max:120'], 'help' => 'Used in page titles, emails and the footer.'],
            'company_tagline' => ['group' => 'branding', 'cast' => 'string', 'label' => 'Tagline', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:160'], 'help' => 'A short line shown under the company name where space allows.'],

            'brand_logo' => ['group' => 'brand_assets', 'cast' => 'string', 'label' => 'Main logo', 'input' => 'image', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'For light backgrounds. Transparent PNG or WebP, at least 80 px wide, up to 2 MB.'],
            'brand_logo_dark' => ['group' => 'brand_assets', 'cast' => 'string', 'label' => 'Logo for dark backgrounds', 'input' => 'image', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'A light version of the logo for dark headers and the footer.'],
            'brand_favicon' => ['group' => 'brand_assets', 'cast' => 'string', 'label' => 'Browser tab icon (favicon)', 'input' => 'image', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Square PNG or WebP, 32 to 1024 px, up to 512 KB.'],
            'brand_logo_mobile' => ['group' => 'brand_assets', 'cast' => 'string', 'label' => 'Mobile logo', 'input' => 'image', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'A compact logo for phones. Falls back to the main logo.'],
            'brand_logo_footer' => ['group' => 'brand_assets', 'cast' => 'string', 'label' => 'Footer logo', 'input' => 'image', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Shown in the website footer. Falls back to the dark-background logo.'],
            'brand_og_image' => ['group' => 'brand_assets', 'cast' => 'string', 'label' => 'Default sharing image', 'input' => 'image', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Shown when a page without its own photo is shared on WhatsApp or Facebook. At least 1200 × 630 px is best.'],

            'admin_login_heading' => ['group' => 'admin_login', 'cast' => 'string', 'label' => 'Heading', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:80'], 'placeholder' => 'Welcome back', 'help' => 'The large line on the staff sign-in screen.'],
            'admin_login_message' => ['group' => 'admin_login', 'cast' => 'string', 'label' => 'Supporting line', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:240'], 'help' => 'For example who to contact when someone cannot sign in.'],
            'admin_login_image' => ['group' => 'admin_login', 'cast' => 'string', 'label' => 'Side image', 'input' => 'image', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'A photo shown beside the sign-in form on larger screens. At least 1200 × 800 px.'],

            'theme_surface' => ['group' => 'theme', 'cast' => 'string', 'label' => 'Dominant · 60% — canvas and surfaces', 'input' => 'color', 'rules' => ['required', self::HEX_COLOUR], 'default' => '#f3f0ea', 'help' => 'Page backgrounds, cards and other large light surfaces.'],
            'theme_dark' => ['group' => 'theme', 'cast' => 'string', 'label' => 'Secondary · 30% — structure and contrast', 'input' => 'color', 'rules' => ['required', self::HEX_COLOUR], 'default' => '#0d1110', 'help' => 'Hero areas, headers, footers and primary text.'],
            'theme_primary' => ['group' => 'theme', 'cast' => 'string', 'label' => 'Accent · 10% — actions and focus', 'input' => 'color', 'rules' => ['required', self::HEX_COLOUR], 'default' => '#1a3328', 'help' => 'Primary buttons, links, active states and focus highlights.'],

            'phone' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Public phone number', 'input' => 'tel', 'rules' => ['required', 'string', new PhoneNumberRule]],
            'whatsapp' => ['group' => 'contact', 'cast' => 'string', 'label' => 'WhatsApp number', 'input' => 'tel', 'rules' => ['nullable', 'string', new PhoneNumberRule], 'help' => 'Leave blank to hide WhatsApp buttons.'],
            'email' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Public email', 'input' => 'email', 'rules' => ['required', 'email:rfc', 'max:255']],
            'address' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Office address', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
            'office_hours' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Office hours', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:120'], 'placeholder' => 'Sat–Thu, 10 am – 7 pm'],
            'map_url' => ['group' => 'contact', 'cast' => 'string', 'label' => 'Google Maps link', 'input' => 'url', 'rules' => ['nullable', 'url:https', 'max:500'], 'help' => 'The “Get directions” link for the office.'],

            'header_cta_label' => ['group' => 'chrome', 'cast' => 'string', 'label' => 'Header button text', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:30'], 'placeholder' => 'Talk to us', 'help' => 'The highlighted button at the top of every page. Leave blank to hide it.'],
            'header_cta_url' => ['group' => 'chrome', 'cast' => 'string', 'label' => 'Header button link', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:255', 'regex:/^(\/[^\s]*|https:\/\/[^\s]+|tel:[0-9+ ]+|mailto:[^\s]+)$/'], 'placeholder' => '/contact', 'help' => 'A page on this website such as /contact, a full https:// address, or tel:/mailto:.'],
            'footer_note' => ['group' => 'chrome', 'cast' => 'string', 'label' => 'Footer statement', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:300'], 'help' => 'The short line beside the logo in the footer.'],
            'footer_copyright' => ['group' => 'chrome', 'cast' => 'string', 'label' => 'Copyright line', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:160'], 'placeholder' => 'All rights reserved.', 'help' => 'Shown after “© year company name”.'],

            'floating_chat_enabled' => ['group' => 'floating', 'cast' => 'bool', 'label' => 'Show the floating chat button', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'floating_chat_desktop' => ['group' => 'floating', 'cast' => 'bool', 'label' => 'Show on desktop', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'floating_chat_mobile' => ['group' => 'floating', 'cast' => 'bool', 'label' => 'Show on phones', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'floating_chat_position' => ['group' => 'floating', 'cast' => 'string', 'label' => 'Corner', 'input' => 'select', 'rules' => ['required', 'in:bottom-right,bottom-left'], 'options' => ['bottom-right' => 'Bottom right', 'bottom-left' => 'Bottom left'], 'default' => 'bottom-right'],
            'floating_chat_message' => ['group' => 'floating', 'cast' => 'string', 'label' => 'Pre-filled WhatsApp message', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:200'], 'placeholder' => 'Hello, I found you on the Urban Haven website.'],
            'messenger_url' => ['group' => 'floating', 'cast' => 'string', 'label' => 'Facebook Messenger link', 'input' => 'url', 'rules' => ['nullable', 'url:https', 'max:255'], 'placeholder' => 'https://m.me/yourpage', 'help' => 'Leave blank to show WhatsApp only.'],

            'sales_phone' => ['group' => 'leads', 'cast' => 'string', 'label' => 'Sales desk phone', 'input' => 'tel', 'rules' => ['nullable', 'string', new PhoneNumberRule], 'help' => 'Used on listings without an assigned contact. Falls back to the public phone.'],
            'sales_inbox_email' => ['group' => 'leads', 'cast' => 'string', 'label' => 'Sales inbox email', 'input' => 'email', 'rules' => ['nullable', 'email:rfc', 'max:255'], 'help' => 'Receives a copy of every new enquiry.'],
            'consent_text' => ['group' => 'leads', 'cast' => 'string', 'label' => 'Enquiry consent wording', 'input' => 'textarea', 'rules' => ['required', 'string', 'max:500']],
            'overdue_digest_enabled' => ['group' => 'leads', 'cast' => 'bool', 'label' => 'Send a morning digest of overdue follow-ups', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'lead_retention_days' => ['group' => 'leads', 'cast' => 'int', 'label' => 'Delete closed leads after (days)', 'input' => 'number', 'rules' => ['required', 'integer', 'min:30', 'max:3650'], 'default' => 730],

            'enable_sale' => ['group' => 'listings', 'cast' => 'bool', 'label' => 'Show properties for sale', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'enable_rent' => ['group' => 'listings', 'cast' => 'bool', 'label' => 'Show properties for rent', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true],
            'address_display_mode' => ['group' => 'listings', 'cast' => 'string', 'label' => 'Public address and map pin', 'input' => 'select', 'rules' => ['required', 'in:exact,approximate,hidden'], 'options' => ['exact' => 'Exact address and pin', 'approximate' => 'Area only, approximate pin', 'hidden' => 'Area only, no map'], 'default' => 'approximate'],
            'coordinate_precision' => ['group' => 'listings', 'cast' => 'int', 'label' => 'Approximate pin precision (decimal places)', 'input' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:4'], 'default' => 2, 'help' => '2 decimals is roughly 1 km; 3 is roughly 100 m.'],

            'analytics_enabled' => ['group' => 'analytics', 'cast' => 'bool', 'label' => 'Tracking is switched on', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true, 'help' => 'Switch off to stop every tracking script at once without deleting the IDs.'],
            'analytics_gtm_id' => ['group' => 'analytics', 'cast' => 'string', 'label' => 'Google Tag Manager container ID', 'input' => 'text', 'rules' => ['nullable', 'regex:/^GTM-[A-Z0-9]{4,10}$/'], 'placeholder' => 'GTM-XXXXXXX', 'help' => 'Leave blank to use the server value, if any.'],
            'analytics_ga4_id' => ['group' => 'analytics', 'cast' => 'string', 'label' => 'Google Analytics 4 measurement ID', 'input' => 'text', 'rules' => ['nullable', 'regex:/^G-[A-Z0-9]{4,12}$/'], 'placeholder' => 'G-XXXXXXXXXX'],
            'analytics_meta_pixel_id' => ['group' => 'analytics', 'cast' => 'string', 'label' => 'Meta (Facebook) Pixel ID', 'input' => 'text', 'rules' => ['nullable', 'regex:/^[0-9]{6,20}$/'], 'placeholder' => '123456789012345'],
            'facebook_app_id' => ['group' => 'analytics', 'cast' => 'string', 'label' => 'Facebook App ID', 'input' => 'text', 'rules' => ['nullable', 'regex:/^[0-9]{6,20}$/'], 'placeholder' => '123456789012345', 'help' => 'Enables the Messenger share option in public share menus.'],
            'analytics_google_ads_id' => ['group' => 'analytics', 'cast' => 'string', 'label' => 'Google Ads conversion ID', 'input' => 'text', 'rules' => ['nullable', 'regex:/^AW-[0-9]{6,14}$/'], 'placeholder' => 'AW-123456789', 'help' => 'Not needed when Google Ads is set up inside Tag Manager.'],

            'consent_banner_enabled' => ['group' => 'consent', 'cast' => 'bool', 'label' => 'Ask visitors before loading tracking', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true, 'help' => 'Switch off only if you are sure no consent is required; tracking then loads for every visitor.'],
            'consent_title' => ['group' => 'consent', 'cast' => 'string', 'label' => 'Banner heading', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:80'], 'placeholder' => 'Analytics cookies'],
            'consent_message' => ['group' => 'consent', 'cast' => 'string', 'label' => 'Banner message', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:300'], 'placeholder' => 'We would like to measure visits and enquiries to improve this site. Nothing is loaded until you agree.'],

            'seo_default_title' => ['group' => 'seo', 'cast' => 'string', 'label' => 'Default page title', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:70'], 'help' => 'Used on pages that have no title of their own. 60 characters or fewer reads best in Google.'],
            'seo_default_description' => ['group' => 'seo', 'cast' => 'string', 'label' => 'Default description', 'input' => 'textarea', 'rules' => ['nullable', 'string', 'max:160'], 'help' => 'The grey text under the title in search results, for pages without their own description.'],
            'seo_google_verification' => ['group' => 'seo', 'cast' => 'string', 'label' => 'Google Search Console verification code', 'input' => 'text', 'rules' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_\-]+$/'], 'help' => 'Only the content value of the meta tag Google gives you.'],
            'seo_allow_indexing' => ['group' => 'seo', 'cast' => 'bool', 'label' => 'Let search engines index the website', 'input' => 'checkbox', 'rules' => ['boolean'], 'default' => true, 'help' => 'Switch off while the site is being prepared. Live production sites should keep this on.'],

            'social_links' => ['group' => 'social', 'cast' => 'json', 'label' => 'Social profile links', 'input' => 'hidden', 'rules' => ['nullable', 'array', 'max:12'], 'default' => []],
            'public_copy' => ['group' => 'website_copy', 'cast' => 'json', 'label' => 'Phrase overrides (JSON)', 'input' => 'json', 'rules' => ['nullable', 'json', 'max:50000'], 'default' => [], 'help' => 'Map each exact current phrase to its replacement. Keep placeholders such as :count and :name intact. Example: {"Explore properties":"Browse homes"}.'],
        ];
    }

    /**
     * Definitions for the given groups, keyed by setting key.
     *
     * @param  list<string>  $groups
     * @return array<string, array<string, mixed>>
     */
    public static function forGroups(array $groups): array
    {
        return array_filter(self::definitions(), fn (array $definition): bool => in_array($definition['group'], $groups, true));
    }

    public static function defaultConsentText(): string
    {
        return 'I agree that Urban Haven may contact me about this enquiry by phone, WhatsApp or email.';
    }
}
