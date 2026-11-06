<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['locale', 'query', 'results_count'];
}
