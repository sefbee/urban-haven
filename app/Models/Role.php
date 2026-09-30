<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    public const OWNER_ADMIN = 'owner_admin';

    public const CONTENT_EDITOR = 'content_editor';

    public const SALES_USER = 'sales_user';

    protected $fillable = ['key', 'label'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
