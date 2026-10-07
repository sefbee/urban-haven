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
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        foreach ([
            ['group' => 'Buying', 'question' => 'Who lists the properties on Urban Haven?', 'answer' => '<p>Every listing is published by the Urban Haven team. There are no third-party sellers or agents, so the people who answer your call are the ones who manage the property.</p>'],
            ['group' => 'Buying', 'question' => 'How do I book a site visit?', 'answer' => '<p>Open any listing and choose a time under "Book a site visit". Our sales team calls you to confirm the slot before you travel.</p>'],
            ['group' => 'Buying', 'question' => 'Is the price shown final?', 'answer' => '<p>The listed price is what the owner is asking today. Registration, utility connection and other government charges are explained by our team before you commit.</p>'],
            ['group' => 'Renting', 'question' => 'How much advance do I need to rent?', 'answer' => '<p>Most rentals ask for one to three months of advance plus the first month of rent. The exact terms are shown on each rental listing.</p>'],
            ['group' => 'Renting', 'question' => 'Can I see whether a property is still available?', 'answer' => '<p>Yes. Every listing shows whether it is available, reserved, sold or rented, and our team updates it as soon as the status changes.</p>'],
        ] as $index => $faq) {
            Faq::query()->firstOrCreate(['question' => $faq['question']], [...$faq, 'sort_order' => $index, 'is_visible' => true]);
        }
    }
}
