<?php

namespace App\Http\Requests\Admin;

class UpdateCmsPageRequest extends StoreCmsPageRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('page')) ?? false;
    }
}
