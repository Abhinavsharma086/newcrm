<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'contact_person', 'phone', 'email', 'address', 'is_active'];
}
