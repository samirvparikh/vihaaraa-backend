<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'title', 'body', 'show_in_footer', 'sort_order', 'is_active'])]
class Page extends Model
{
    protected function casts(): array
    {
        return ['show_in_footer' => 'boolean', 'is_active' => 'boolean'];
    }

    /** Body is stored as plain text; blank lines separate paragraphs. */
    public function paragraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', $this->body))));
    }
}
