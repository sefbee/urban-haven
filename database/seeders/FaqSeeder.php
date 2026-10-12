<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Starter questions for local development; production FAQs are written in the admin.
     */
    public function run(): void
    {
        $faqs = [
            [
                'group' => 'Buying',
                'question' => 'Who lists the properties on Urban Haven?',
                'answer' => '<p>Every listing is directly published, verified, and represented by the Urban Haven in-house team. There are no unverified third-party brokers, so the property consultants who answer your inquiries are the exact team members who inspect the site and handle the legal documentation.</p>',
            ],
            [
                'group' => 'Buying',
                'question' => 'How do I book an in-person site visit?',
                'answer' => '<p>You can schedule a visit on any property or project page by selecting your preferred date and time under "Book a Visit", or by contacting our client desk via phone or WhatsApp. Our sales consultants will confirm the appointment and accompany you throughout the private tour.</p>',
            ],
            [
                'group' => 'Buying',
                'question' => 'Are listed prices transparent and inclusive of all fees?',
                'answer' => '<p>The listed price represents the direct asking price. Government stamp duties, registration fees, mutation charges, and utility connection fees (DESCO/DPDC, Titas Gas, WASA) are calculated separately and presented in a transparent breakdown prior to any formal agreement.</p>',
            ],
            [
                'group' => 'Buying',
                'question' => 'What documents do I need to prepare to purchase a property?',
                'answer' => '<p>To initiate a purchase, you will need your National ID (NID) or Passport copy, passport-sized photographs, and TIN certificate. Our legal team will facilitate title search, CS/SA/RS/BS khatian verification, non-encumbrance certificate, and RAJUK plan approval checks.</p>',
            ],
            [
                'group' => 'Renting',
                'question' => 'What is the standard advance deposit required for renting in Dhaka?',
                'answer' => '<p>Standard residential leases in Gulshan, Banani, and Dhanmondi typically require between one to three months of advance security deposit plus the first month’s rent. Commercial leases may require 3 to 6 months of security deposit depending on fit-out allowances.</p>',
            ],
            [
                'group' => 'Renting',
                'question' => 'Are service charges and utility bills included in the monthly rent?',
                'answer' => '<p>Monthly building maintenance fees (covering 24/7 security, lift operation, generator fuel, and common area cleaning) are clearly itemized on our property cards. Utility consumption (electricity and piped or LPG gas) is metered individually.</p>',
            ],
            [
                'group' => 'Legal & Finance',
                'question' => 'Does Urban Haven assist with home loan financing?',
                'answer' => '<p>Yes. We have established institutional tie-ups with leading banks and financial institutions in Bangladesh (including DBH, IDLC, BRAC Bank, and Standard Chartered) offering preferential home loan interest rates, accelerated processing, and pre-approval assessments.</p>',
            ],
            [
                'group' => 'Legal & Finance',
                'question' => 'How does Urban Haven verify property land titles and RAJUK approvals?',
                'answer' => '<p>Our in-house legal department conducts a rigorous 7-point due diligence check before any listing or project is taken on board, inspecting the chain of title deeds (Baya Deed), RS/City Jorip records, Mutation Khatian, DCR, and RAJUK approved structural and architectural drawings.</p>',
            ],
            [
                'group' => 'Projects & Handover',
                'question' => 'What happens if a development handover is delayed?',
                'answer' => '<p>All Urban Haven affiliated developer agreements include a legally binding Real Estate Development & Management Act (REHAB) clause with guaranteed delay compensation penalty provisions to protect client investments.</p>',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::query()->updateOrCreate(
                ['question' => $faq['question']],
                [
                    'group' => $faq['group'],
                    'answer' => $faq['answer'],
                    'sort_order' => $index,
                    'is_visible' => true,
                ]
            );
        }
    }
}
