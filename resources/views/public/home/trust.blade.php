@php($whatsapp = preg_replace('/\D+/', '', (string) config('urbanhaven.whatsapp.number')))

<section class="border-t border-line bg-paper">
    <div class="uh-container uh-section-tight">
        <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['icon' => 'document', 'title' => __('Verified legal documents')],
                ['icon' => 'tag', 'title' => __('No hidden listing fees')],
                ['icon' => 'users', 'title' => __('Direct desk support')],
                ['icon' => 'shield', 'title' => __('Legal consultation available')],
            ] as $badge)
                <li class="flex items-center gap-3 rounded-xl bg-cream px-4 py-4 ring-1 ring-line">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald/10 text-emerald">
                        <x-icon :name="$badge['icon']" class="size-5" />
                    </span>
                    <span class="text-sm font-semibold leading-snug">{{ $badge['title'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>

<section class="uh-hero-flat text-cream">
    <div class="uh-container flex flex-col items-start gap-7 py-14 md:flex-row md:items-center md:justify-between md:py-16">
        <div class="max-w-xl">
            <h2 class="uh-h2 text-cream">{{ __('Tell us what you are looking for') }}</h2>
            <p class="mt-3 text-cream/80">{{ __('Send a short brief and our sales desk will come back with the homes that actually match it.') }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a class="uh-btn-gold uh-btn-lg" href="{{ route('cms.show', 'contact') }}">{{ __('Contact Urban Haven Agent') }}</a>
            @if(filled($whatsapp))
                <a class="uh-btn-ondark uh-btn-lg" href="https://wa.me/{{ $whatsapp }}" rel="noopener">
                    <x-icon name="whatsapp" class="size-4" />
                    {{ __('WhatsApp Us') }}
                </a>
            @endif
        </div>
    </div>
</section>
