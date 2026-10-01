<?php

namespace App\Contracts;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

interface MediaService
{
    public function store(Model $owner, UploadedFile $file, string $collection, ?string $altText = null): Media;

    /**
     * Alt text is mandatory before an image can be made public.
     */
    public function updateDetails(Media $media, ?string $altText, bool $isPublic, User $actor): Media;

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(Model $owner, string $collection, array $orderedIds): void;

    public function delete(Media $media, User $actor): void;
}
