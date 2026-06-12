<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    protected $fillable = [
        'version',
        'changelog',
        'zip_path',
        'signature',
        'size',
    ];
}
