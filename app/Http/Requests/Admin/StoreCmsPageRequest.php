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
