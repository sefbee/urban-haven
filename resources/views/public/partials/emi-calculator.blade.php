@php
    $starting = (float) ($price ?? 0);
    $editable = $editable ?? false;
    $headingId = $headingId ?? 'emi-heading';
@endphp

<section class="uh-emi" x-data="uhEmi({{ $starting }})" aria-labelledby="{{ $headingId }}">
    <div class="uh-emi-head">
        <h2 id="{{ $headingId }}" class="uh-h2">{{ __('EMI Loan Calculator') }}</h2>
        <p class="uh-chapter-lede">{{ __('Indicative reducing-balance EMI in BDT. Confirm the rate with your bank.') }}</p>
    </div>

    <div class="uh-emi-body">
        <div class="grid content-start gap-7">
            @if($editable)
                <label class="block">
                    <span class="uh-label">{{ __('Property price') }}</span>
                    <input type="number" min="0" step="10000" class="uh-input" x-model.number="price" inputmode="numeric">
                </label>
            @endif

            <label class="block">
                <span class="flex items-center justify-between gap-3">
                    <span class="uh-label mb-0">{{ __('Down Payment') }}</span>
                    <span class="uh-emi-value"><span class="uh-numeric" x-text="downPct"></span>%</span>
                </span>
                <input type="range" class="uh-range mt-3" min="0" max="80" step="5" x-model.number="downPct">
                <p class="mt-2 text-sm text-[var(--uh-faint)]">{{ __('BDT') }} <span class="uh-numeric" x-text="format(downPayment)"></span></p>
            </label>

            <label class="block">
                <span class="flex items-center justify-between gap-3">
                    <span class="uh-label mb-0">{{ __('Interest Rate') }}</span>
                    <span class="uh-emi-value"><span class="uh-numeric" x-text="rate"></span>%</span>
                </span>
                <input type="range" class="uh-range mt-3" min="6" max="16" step="0.25" x-model.number="rate">
            </label>

            <label class="block">
                <span class="flex items-center justify-between gap-3">
                    <span class="uh-label mb-0">{{ __('Loan Tenure') }}</span>
                    <span class="uh-emi-value"><span class="uh-numeric" x-text="years"></span> {{ __('years') }}</span>
                </span>
                <input type="range" class="uh-range mt-3" min="5" max="25" step="1" x-model.number="years">
            </label>
        </div>

        <dl class="uh-emi-readout">
            <div class="is-primary">
                <dt>{{ __('Monthly EMI') }}</dt>
                <dd>{{ __('BDT') }} <span class="uh-numeric" x-text="format(emi)"></span></dd>
            </div>
            <div>
                <dt>{{ __('Loan Amount') }}</dt>
                <dd>{{ __('BDT') }} <span class="uh-numeric" x-text="format(principal)"></span></dd>
            </div>
            <div>
                <dt>{{ __('Total interest') }}</dt>
                <dd>{{ __('BDT') }} <span class="uh-numeric" x-text="format(totalInterest)"></span></dd>
            </div>
        </dl>
    </div>
</section>
