<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TingkatKelas extends Model
{
    protected $table = 'tingkat_kelas';

    public $timestamps = false;

    protected $fillable = [
        'angka',
        'romawi',
        'ikon',
    ];
}
