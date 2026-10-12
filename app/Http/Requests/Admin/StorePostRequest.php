<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use App\Support\SeoFields;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        $canManagePost = $post instanceof Post
            ? $this->user()?->can('update', $post) ?? false
            : $this->user()?->can('create', Post::class) ?? false;

        if (! $canManagePost) {
            return false;
        }

        return $this->input('submit_action') !== 'publish'
            || ($this->user()?->can('publish', new Post) ?? false);
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
        $post = $this->route('post');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => SeoFields::slugRules('posts', $post instanceof Post ? $post : null),
            'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['nullable', 'string', 'max:200000'],
            'author_label' => ['nullable', 'string', 'max:120'],
            'related_post_ids' => ['nullable', 'array', 'max:6'],
            'related_post_ids.*' => ['integer', 'exists:posts,id'],
            'cover_images' => $this->isMethod('POST')
                ? ['nullable', 'array', 'max:100']
                : ['prohibited'],
            'cover_images.*' => $this->isMethod('POST')
                ? ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('urbanhaven.media.max_image_kb')]
                : ['prohibited'],
            'cover_images_meta' => $this->isMethod('POST')
                ? ['sometimes', 'array', 'max:100']
                : ['prohibited'],
            'cover_images_meta.*.alt_text' => $this->isMethod('POST')
                ? ['nullable', 'string', 'max:200']
                : ['prohibited'],
            'submit_action' => ['nullable', 'in:draft,publish'],
            ...SeoFields::rules(),
        ];
    }

    /**
     * The article columns, without the search engine inputs. A blank permalink keeps the current one.
     *
     * @return array<string, mixed>
     */
    public function content(): array
    {
        $content = $this->safe()->except([
            ...SeoFields::inputNames(),
            'cover_images',
            'cover_images_meta',
            'submit_action',
        ]);

        if (blank($content['slug'] ?? null)) {
            unset($content['slug']);
        }

        return $content;
    }
}
