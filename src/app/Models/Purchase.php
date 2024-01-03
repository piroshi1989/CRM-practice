<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Item;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status'
    ];

    public function Customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function Items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
        //中間テーブルのリレーションを指定
        //withPivotの中には中間テーブルにしかない値を代入する
    }
}
