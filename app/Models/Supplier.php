<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'gst_number', 'contact_person', 'phone', 'phone_2', 'email', 'address', 'is_active'];
}
