@php
    $starting = (float) ($price ?? 0);
    $editable = $editable ?? false;
    $headingId = $headingId ?? 'emi-heading';
@endphp

<section class="uh-panel" x-data="uhEmi({{ $starting }})" aria-labelledby="{{ $headingId }}">
    <div class="flex items-start gap-3">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-forest/10 text-forest">
            <x-icon name="calculator" class="size-5" />
        </span>
        <div>
            <h2 id="{{ $headingId }}" class="uh-h3">{{ __('EMI Loan Calculator') }}</h2>
            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ __('Indicative reducing-balance EMI in BDT. Confirm the rate with your bank.') }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-5">
        @if($editable)
            <label class="block">
                <span class="uh-label">{{ __('Property price') }}</span>
                <input type="number" min="0" step="10000" class="uh-input" x-model.number="price" inputmode="numeric">
            </label>
        @endif

        <div>
            <p class="uh-label">{{ __('Loan Amount') }}</p>
            <p class="text-lg font-semibold text-forest">BDT <span class="uh-numeric" x-text="format(principal)"></span></p>
        </div>

        <label class="block">
            <span class="flex items-center justify-between gap-3">
                <span class="uh-label mb-0">{{ __('Down Payment') }}</span>
                <span class="text-sm font-semibold"><span class="uh-numeric" x-text="downPct"></span>%</span>
            </span>
            <input type="range" class="uh-range mt-2" min="0" max="80" step="5" x-model.number="downPct">
            <p class="mt-1 text-xs text-[var(--color-muted)]">BDT <span class="uh-numeric" x-text="format(downPayment)"></span></p>
        </label>

        <label class="block">
            <span class="flex items-center justify-between gap-3">
                <span class="uh-label mb-0">{{ __('Interest Rate') }}</span>
                <span class="text-sm font-semibold"><span class="uh-numeric" x-text="rate"></span>%</span>
            </span>
            <input type="range" class="uh-range mt-2" min="6" max="16" step="0.25" x-model.number="rate">
        </label>

        <label class="block">
            <span class="flex items-center justify-between gap-3">
                <span class="uh-label mb-0">{{ __('Loan Tenure') }}</span>
                <span class="text-sm font-semibold"><span class="uh-numeric" x-text="years"></span> {{ __('years') }}</span>
            </span>
            <input type="range" class="uh-range mt-2" min="5" max="25" step="1" x-model.number="years">
        </label>
    </div>

    <dl class="mt-6 grid grid-cols-2 gap-3 rounded-xl bg-sand p-4 text-sm">
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-[var(--color-muted)]">{{ __('Monthly EMI') }}</dt>
            <dd class="mt-1 text-lg font-semibold text-forest">BDT <span class="uh-numeric" x-text="format(emi)"></span></dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-[var(--color-muted)]">{{ __('Total interest') }}</dt>
            <dd class="mt-1 font-semibold">BDT <span class="uh-numeric" x-text="format(totalInterest)"></span></dd>
        </div>
    </dl>
</section>
