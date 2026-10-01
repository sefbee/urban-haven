<section class="bg-hero text-white">
    <div class="uh-container py-10 md:py-14 lg:py-16">
        <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-7">
                <h1 class="uh-h1 max-w-xl text-white">
                    {{ __('Find a place that feels like home.') }}
                </h1>
                <p class="mt-4 max-w-lg text-sm leading-relaxed text-white/65 sm:text-base">
                    {{ __('Explore verified properties and carefully selected projects from Urban Haven.') }}
                </p>

                <form method="GET" action="{{ route('properties.index') }}"
                      class="mt-7 max-w-2xl"
                      x-data="uhHeroSearch"
                      @submit="submit(); $event.target.querySelectorAll('input, select').forEach((field) => { if (field.value === '' && field.type !== 'hidden') field.disabled = true })">
                    <h2 class="sr-only">{{ __('Search properties') }}</h2>

                    <div class="flex flex-wrap gap-2" role="tablist" aria-label="{{ __('Purpose') }}">
                        <button type="button"
                                class="inline-flex min-h-9 items-center rounded-md border px-4 text-xs font-bold uppercase tracking-wide transition"
                                :class="purpose === 'sale' ? 'border-emerald bg-emerald text-white' : 'border-white/20 bg-transparent text-white/75 hover:border-white/40'"
                                :aria-pressed="(purpose === 'sale').toString()"
                                @click="setPurpose('sale')">{{ __('Buy') }}</button>
                        <button type="button"
                                class="inline-flex min-h-9 items-center rounded-md border px-4 text-xs font-bold uppercase tracking-wide transition"
                                :class="purpose === 'rent' ? 'border-emerald bg-emerald text-white' : 'border-white/20 bg-transparent text-white/75 hover:border-white/40'"
                                :aria-pressed="(purpose === 'rent').toString()"
                                @click="setPurpose('rent')">{{ __('Rent') }}</button>
                        <input type="hidden" name="listing_type" :value="purpose">
                    </div>

                    <div class="mt-4 space-y-3 rounded-xl border border-white/10 bg-hero-muted/80 p-4 sm:p-5">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="block">
                                <span class="mb-1.5 block text-xs font-medium text-white/55">{{ __('Location') }}</span>
                                <select name="location_area_id" class="uh-hero-select">
                                    <option value="">{{ __('Any location') }}</option>
                                    @foreach($heroAreas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}@if($area->city), {{ $area->city }}@endif</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="mb-1.5 block text-xs font-medium text-white/55">{{ __('Property type') }}</span>
                                <select name="property_type_id" class="uh-hero-select">
                                    <option value="">{{ __('Any type') }}</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block">
                                <span class="mb-1.5 block text-xs font-medium text-white/55">{{ __('Bedrooms') }}</span>
                                <select name="min_beds" class="uh-hero-select">
                                    <option value="">{{ __('Any') }}</option>
                                    @foreach([1, 2, 3, 4] as $beds)
                                        <option value="{{ $beds }}">{{ __(':count+ bedrooms', ['count' => $beds]) }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-white/55">{{ __('Price range') }}</p>
                            <div class="uh-dual-range mt-3">
                                <div class="uh-dual-range-track">
                                    <div class="uh-dual-range-fill" :style="fillStyle"></div>
                                </div>
                                <input type="range" min="0" :max="ceiling" :step="step" x-model.number="min"
                                       @input="clampMin" :aria-valuemin="0" :aria-valuemax="max"
                                       aria-label="{{ __('Minimum') }}">
                                <input type="range" min="0" :max="ceiling" :step="step" x-model.number="max"
                                       @input="clampMax" :aria-valuemin="min" :aria-valuemax="ceiling"
                                       aria-label="{{ __('Maximum') }}">
                            </div>
                            <input type="hidden" name="min_price" :value="min" :disabled="min <= 0">
                            <input type="hidden" name="max_price" :value="max" :disabled="max >= ceiling">
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <label class="block">
                                    <span class="mb-1 block text-[0.6875rem] text-white/45">{{ __('Minimum') }}</span>
                                    <span class="relative block">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-white/50">৳</span>
                                        <input type="number" min="0" :max="max" :step="step"
                                               class="uh-hero-input pl-8 uh-numeric" x-model.number="min"
                                               @change="clampMin" inputmode="numeric">
                                    </span>
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-[0.6875rem] text-white/45">{{ __('Maximum') }}</span>
                                    <span class="relative block">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-white/50">৳</span>
                                        <input type="number" min="0" :max="ceiling" :step="step"
                                               class="uh-hero-input pl-8 uh-numeric" x-model.number="max"
                                               @change="clampMax" inputmode="numeric">
                                    </span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="uh-btn-emerald" :disabled="submitting">
                            <x-icon name="search" class="size-4" x-show="!submitting" />
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                            {{ __('Search') }}
                        </button>
                    </div>
                </form>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a class="uh-btn-ondark uh-btn-sm" href="{{ route('properties.index') }}">{{ __('Explore Properties') }}</a>
                    <a class="uh-btn-ondark uh-btn-sm" href="{{ route('projects.index') }}">{{ __('Explore Projects') }}</a>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="overflow-hidden rounded-2xl bg-hero-muted ring-1 ring-white/10">
                    @if($heroImage)
                        <img src="{{ $heroImage->url(1280) }}"
                             srcset="{{ $heroImage->url(768) }} 768w, {{ $heroImage->url(1280) }} 1280w"
                             sizes="(min-width: 1024px) 40vw, 100vw"
                             alt="{{ $heroImageAlt }}" fetchpriority="high" decoding="async"
                             class="aspect-4/3 size-full object-cover">
                    @else
                        <div class="flex aspect-4/3 items-center justify-center bg-linear-to-br from-forest via-hero-muted to-hero">
                            <x-icon name="home" class="size-16 text-white/25" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
