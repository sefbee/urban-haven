<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post
            ? $this->user()?->can('update', $post) ?? false
            : $this->user()?->can('create', Post::class) ?? false;
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
        $post = $this->route('post');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('posts', 'slug')->ignore($post instanceof Post ? $post->id : null)],
            'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['nullable', 'string', 'max:200000'],
            'author_label' => ['nullable', 'string', 'max:120'],
            'related_post_ids' => ['nullable', 'array', 'max:6'],
            'related_post_ids.*' => ['integer', 'exists:posts,id'],
        ];
    }
}
