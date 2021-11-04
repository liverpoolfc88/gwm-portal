<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class II001File extends Model
{
    protected $table="ii001_files";

    protected $fillable = [
        'filename', 'recordcount', 'status'
    ];
}
