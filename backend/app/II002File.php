<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class II002File extends Model
{
    protected $table="ii002_files";

    protected $fillable = [
        'filename', 'recordcount', 'status'
    ];
}
