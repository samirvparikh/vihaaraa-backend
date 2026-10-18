<?php

namespace App\Http\Controllers\Admin;

use App\Support\Media;
use Illuminate\Http\Request;

trait HandlesImages
{
    /** Resolves an image field: keeps the current file, replaces it with an upload, or removes it. */
    protected function image(Request $request, string $field, ?string $current, string $folder): ?string
    {
        if ($request->hasFile($field)) {
            return Media::store($request->file($field), $folder, $current);
        }
        if ($request->boolean('remove_'.$field)) {
            Media::delete($current);

            return null;
        }

        return $current;
    }

    protected function imageRule(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'];
    }
}
