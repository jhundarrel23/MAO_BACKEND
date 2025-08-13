<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable (for create/update).
     */
    protected $fillable = [
        'sector_id',            // Use sector_id consistently
        'fname',
        'mname',
        'lname',
        'extension_name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden when returned in JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be type-casted.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the sector that the user belongs to.
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }
}
