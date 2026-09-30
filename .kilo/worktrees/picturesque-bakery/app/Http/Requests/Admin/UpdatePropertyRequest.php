<?php

namespace App\Http\Requests\Admin;

class UpdatePropertyRequest extends StorePropertyRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('property')) ?? false;
    }
}
