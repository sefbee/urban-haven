@php
    /**
     * Shared search engine panel.
     *
     * @var array{focus_keyword?: ?string, meta_title?: ?string, meta_description?: ?string, gsc_code?: ?string, noindex?: bool}|\App\Models\SeoOverride|null $seo
     */
    $seo = $seo ?? null;
    $seo = $seo instanceof \App\Models\SeoOverride ? $seo->only(['focus_keyword', 'meta_title', 'meta_description', 'gsc_code', 'noindex']) : (array) $seo;
    $prefix = $prefix ?? '';
    $idPrefix = $idPrefix ?? 'seo';
    $slugName = $slugName ?? null;
    $slug = $slug ?? null;
    $baseUrl = $baseUrl ?? url('/');
    $baseUrl = str_ends_with($baseUrl, '=') ? $baseUrl : rtrim($baseUrl, '/').'/';
    $staticPath = $staticPath ?? null;
    $titleSource = $titleSource ?? 'title';
    $contentSource = $contentSource ?? null;
    $autoSlug = $autoSlug ?? blank($slug);
    $fallbackTitle = $fallbackTitle ?? '';
    $fallbackDescription = $fallbackDescription ?? '';
    $heading = $heading ?? 'Search engine (SEO)';
    $siteName = \App\Models\Setting::get('company_name', config('app.name'));

    $name = fn (string $field): string => $prefix === '' ? $field : $prefix.'['.$field.']';
    $key = fn (string $field): string => $prefix === '' ? $field : str_replace(['[', ']'], ['.', ''], $prefix).'.'.$field;
    $old = fn (string $field, mixed $default) => old($key($field), $default);
    $state = [
        'keyword' => (string) $old('seo_focus_keyword', $seo['focus_keyword'] ?? ''),
        'title' => (string) $old('seo_meta_title', $seo['meta_title'] ?? ''),
        'description' => (string) $old('seo_meta_description', $seo['meta_description'] ?? ''),
        'slug' => (string) ($slugName ? old($slugName, $slug) : ($slug ?? '')),
        'autoSlug' => (bool) ($slugName && $autoSlug && blank(old($slugName, $slug))),
        'titleSource' => $titleSource,
        'contentSource' => $contentSource,
        'fallbackTitle' => $fallbackTitle,
        'fallbackDescription' => $fallbackDescription,
        'baseUrl' => $baseUrl,
        'staticPath' => $staticPath,
        'siteName' => $siteName,
        'titleMax' => \App\Support\SeoFields::TITLE_MAX,
        'descriptionMax' => \App\Support\SeoFields::DESCRIPTION_MAX,
    ];
@endphp

<section class="uh-panel dd-seo" x-data="uhSeo(@js($state))" aria-labelledby="{{ $idPrefix }}-heading">
    <div class="dd-seo-head">
        <div>
            <h2 id="{{ $idPrefix }}-heading" class="uh-h4">{{ $heading }}</h2>
            <p class="mt-1 text-xs text-[var(--color-muted)]">How this page appears on Google. Blank fields fall back to the page’s own title and text.</p>
        </div>
        <span class="dd-seo-score" :class="'is-' + grade" x-cloak>
            <span x-text="passed + '/' + checks.length"></span>
            <span x-text="gradeLabel"></span>
        </span>
    </div>

    <div class="dd-seo-preview" aria-hidden="true">
        <p class="dd-seo-preview-site"><span class="dd-seo-preview-favicon"></span><span x-text="siteName"></span></p>
        <p class="dd-seo-preview-url" x-text="previewUrl"></p>
        <p class="dd-seo-preview-title" x-text="previewTitle"></p>
        <p class="dd-seo-preview-desc" x-text="previewDescription"></p>
    </div>

    <div class="dd-seo-fields">
        <div class="uh-field">
            <label class="uh-label" for="{{ $idPrefix }}-keyword">1. Focus keyword <span class="uh-label-optional">optional</span></label>
            <input id="{{ $idPrefix }}-keyword" name="{{ $name('seo_focus_keyword') }}" class="uh-input" maxlength="80" x-model="keyword"
                   placeholder="e.g. apartment for sale in Gulshan">
            <p class="uh-hint">The search phrase this page should rank for. Used for the checks below; it is not shown publicly.</p>
            @error($key('seo_focus_keyword'))<p class="uh-error">{{ $message }}</p>@enderror
        </div>

        <div class="uh-field">
            <div class="dd-seo-label-row">
                <label class="uh-label" for="{{ $idPrefix }}-title">2. Meta title <span class="uh-label-optional">optional</span></label>
                <span class="dd-seo-count" :class="titleTone" x-text="(title || autoTitle).length + ' / 60'"></span>
            </div>
            <input id="{{ $idPrefix }}-title" name="{{ $name('seo_meta_title') }}" class="uh-input" maxlength="{{ \App\Support\SeoFields::TITLE_MAX }}" x-model="title"
                   :placeholder="autoTitle">
            <div class="dd-seo-meter"><span :class="titleTone" :style="'width:' + Math.min(100, (title || autoTitle).length / 60 * 100) + '%'"></span></div>
            <p class="uh-hint">The blue headline on Google. 30–60 characters works best.</p>
            @error($key('seo_meta_title'))<p class="uh-error">{{ $message }}</p>@enderror
        </div>

        <div class="uh-field">
            <div class="dd-seo-label-row">
                <label class="uh-label" for="{{ $idPrefix }}-description">3. Meta description <span class="uh-label-optional">optional</span></label>
                <span class="dd-seo-count" :class="descriptionTone" x-text="(description || autoDescription).length + ' / 160'"></span>
            </div>
            <textarea id="{{ $idPrefix }}-description" name="{{ $name('seo_meta_description') }}" class="uh-input uh-textarea" rows="3"
                      maxlength="{{ \App\Support\SeoFields::DESCRIPTION_MAX }}" x-model="description" :placeholder="autoDescription"></textarea>
            <div class="dd-seo-meter"><span :class="descriptionTone" :style="'width:' + Math.min(100, (description || autoDescription).length / 160 * 100) + '%'"></span></div>
            <p class="uh-hint">The grey text under the headline. 120–160 characters, with the focus keyword once.</p>
            @error($key('seo_meta_description'))<p class="uh-error">{{ $message }}</p>@enderror
        </div>

        <div class="uh-field">
            <label class="uh-label" @if($slugName) for="{{ $idPrefix }}-slug" @endif>4. Permalink / URL</label>
            @if($slugName)
                <div class="dd-seo-permalink">
                    <span class="dd-seo-permalink-base" dir="ltr">{{ $baseUrl }}</span>
                    <input id="{{ $idPrefix }}-slug" name="{{ $slugName }}" class="dd-seo-permalink-input" dir="ltr" maxlength="120" x-model="slug"
                           @input="slugEdited()" @blur="slug = slugify(slug)" placeholder="generated-from-the-title" autocomplete="off" spellcheck="false">
                    <button type="button" class="dd-seo-permalink-btn" @click="regenerate()" title="Generate from the title">
                        <x-icon name="refresh" class="size-3.5" />
                        <span class="sr-only">Generate from the title</span>
                    </button>
                </div>
                <p class="uh-hint" x-show="autoSlug">Generated from the title as you type. Edit it to set your own.</p>
                <p class="uh-hint" x-show="! autoSlug" x-cloak>Lowercase letters, numbers and dashes. Changing the URL of a live page adds a redirect from the old one automatically.</p>
                @error($slugName)<p class="uh-error">{{ $message }}</p>@enderror
            @else
                <div class="dd-seo-permalink is-static">
                    <span class="dd-seo-permalink-base" dir="ltr" x-text="previewUrl"></span>
                    <button type="button" class="dd-seo-permalink-btn" @click="copy()" :title="copied ? 'Copied' : 'Copy URL'">
                        <x-icon name="copy" class="size-3.5" />
                        <span class="sr-only">Copy URL</span>
                    </button>
                </div>
                <p class="uh-hint">This page’s address is fixed.</p>
            @endif
        </div>

        <div class="uh-field">
            <label class="uh-label" for="{{ $idPrefix }}-gsc">5. Google Search Console (GSC) code <span class="uh-label-optional">optional</span></label>
            <input id="{{ $idPrefix }}-gsc" name="{{ $name('seo_gsc_code') }}" class="uh-input" maxlength="100" dir="ltr"
                   value="{{ $old('seo_gsc_code', $seo['gsc_code'] ?? '') }}" placeholder="abc123XYZ_verification-code">
            <p class="uh-hint">Only the content value from Google’s <code>google-site-verification</code> meta tag, when Search Console asks you to verify this exact page. The site-wide code lives in Settings › SEO.</p>
            @error($key('seo_gsc_code'))<p class="uh-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <input type="hidden" name="{{ $name('seo_noindex') }}" value="0">
            <label class="uh-check">
                <input type="checkbox" name="{{ $name('seo_noindex') }}" value="1" @checked($old('seo_noindex', $seo['noindex'] ?? false))>
                <span>Hide this page from search engines (noindex)</span>
            </label>
        </div>
    </div>

    <ul class="dd-seo-checks" aria-label="SEO checks">
        <template x-for="check in checks" :key="check.label">
            <li :class="check.ok ? 'is-ok' : 'is-todo'">
                <span class="dd-seo-check-dot" aria-hidden="true"></span>
                <span x-text="check.label"></span>
            </li>
        </template>
    </ul>
</section>
