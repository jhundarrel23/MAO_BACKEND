<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ Import this trait

class Sector extends Model
{
    use HasFactory, SoftDeletes; // ✅ Enable soft delete support

    protected $table = 'sector';

    protected $fillable = [
        'sector_name',
        'status',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
