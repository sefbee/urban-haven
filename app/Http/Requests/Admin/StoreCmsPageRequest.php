<?php

namespace App\Http\Requests\Admin;

use App\Models\CmsPage;
use App\Support\SeoFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCmsPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CmsPage::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => SeoFields::normaliseSlug($this->input('slug'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $page = $this->route('page');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => SeoFields::slugRules('cms_pages', $page instanceof CmsPage ? $page : null, SeoFields::RESERVED_SLUGS),
            'template' => ['nullable', Rule::in(CmsPage::TEMPLATES)],
            'body' => ['nullable', 'string', 'max:200000'],
            'layout_content' => ['nullable', 'array:hero_eyebrow,hero_title,hero_intro,hero_quote,hero_quote_kicker,hero_quote_label,hero_badge,hero_primary_cta,hero_primary_url,hero_secondary_cta,hero_secondary_url,story_eyebrow,story_title,values_eyebrow,values_title,values_intro,value_one_title,value_one_text,value_two_title,value_two_text,value_three_title,value_three_text,cta_eyebrow,cta_title,cta_body,cta_label,cta_url'],
            'layout_content.hero_eyebrow' => ['nullable', 'string', 'max:80'],
            'layout_content.hero_title' => ['nullable', 'string', 'max:120'],
            'layout_content.hero_intro' => ['nullable', 'string', 'max:300'],
            'layout_content.hero_quote' => ['nullable', 'string', 'max:240'],
            'layout_content.hero_quote_kicker' => ['nullable', 'string', 'max:80'],
            'layout_content.hero_quote_label' => ['nullable', 'string', 'max:80'],
            'layout_content.hero_badge' => ['nullable', 'string', 'max:100'],
            'layout_content.hero_primary_cta' => ['nullable', 'string', 'max:40'],
            'layout_content.hero_primary_url' => ['nullable', 'string', 'max:255', 'regex:/^\/(?!\/)[\S]*$/'],
            'layout_content.hero_secondary_cta' => ['nullable', 'string', 'max:40'],
            'layout_content.hero_secondary_url' => ['nullable', 'string', 'max:255', 'regex:/^\/(?!\/)[\S]*$/'],
            'layout_content.story_eyebrow' => ['nullable', 'string', 'max:80'],
            'layout_content.story_title' => ['nullable', 'string', 'max:140'],
            'layout_content.values_eyebrow' => ['nullable', 'string', 'max:80'],
            'layout_content.values_title' => ['nullable', 'string', 'max:140'],
            'layout_content.values_intro' => ['nullable', 'string', 'max:300'],
            'layout_content.value_one_title' => ['nullable', 'string', 'max:80'],
            'layout_content.value_one_text' => ['nullable', 'string', 'max:240'],
            'layout_content.value_two_title' => ['nullable', 'string', 'max:80'],
            'layout_content.value_two_text' => ['nullable', 'string', 'max:240'],
            'layout_content.value_three_title' => ['nullable', 'string', 'max:80'],
            'layout_content.value_three_text' => ['nullable', 'string', 'max:240'],
            'layout_content.cta_eyebrow' => ['nullable', 'string', 'max:80'],
            'layout_content.cta_title' => ['nullable', 'string', 'max:140'],
            'layout_content.cta_body' => ['nullable', 'string', 'max:300'],
            'layout_content.cta_label' => ['nullable', 'string', 'max:40'],
            'layout_content.cta_url' => ['nullable', 'string', 'max:255', 'regex:/^\/(?!\/)[\S]*$/'],
            ...SeoFields::rules(),
        ];
    }

    /**
     * The page columns, without the search engine inputs. A blank permalink keeps the current one.
     *
     * @return array<string, mixed>
     */
    public function content(): array
    {
        $content = $this->safe()->except(SeoFields::inputNames());

        if (blank($content['slug'] ?? null)) {
            unset($content['slug']);
        }

        return $content;
    }
}
