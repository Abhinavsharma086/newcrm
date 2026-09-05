<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $fillable = ['name', 'contact_person', 'phone', 'type', 'is_active'];

    public function rfcCustomers()
    {
        return $this->hasMany(Customer::class, 'rfc_contractor_id');
    }

    public function jmrCustomers()
    {
        return $this->hasMany(Customer::class, 'jmr_contractor_id');
    }
}
