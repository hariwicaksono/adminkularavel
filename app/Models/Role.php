<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relasi ke tabel users (many to many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Relasi ke tabel permissions (many to many)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
