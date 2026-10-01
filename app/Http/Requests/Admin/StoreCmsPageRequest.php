<?php

namespace App\Http\Requests\Admin;

use App\Models\CmsPage;
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
        if ($this->filled('slug')) {
            $this->merge(['slug' => str($this->input('slug'))->slug()->toString()]);
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
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('cms_pages', 'slug')->ignore($page instanceof CmsPage ? $page->id : null), Rule::notIn(['admin', 'properties', 'projects', 'locations', 'articles', 'faq', 'shortlist', 'compare'])],
            'template' => ['nullable', Rule::in(CmsPage::TEMPLATES)],
            'body' => ['nullable', 'string', 'max:200000'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }
}
