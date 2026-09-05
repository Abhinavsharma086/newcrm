<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'manager_id', 'branch_id'];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }
}
