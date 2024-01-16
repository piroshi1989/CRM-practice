<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\Subtotal;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new Subtotal);
    }

    //分析において何年何月日から何年何月日までなどを指定するメソッド
    public function scopeBetweenDate($query, $startDate = null, $endDate = null)
    {
        if(is_null($startDate) && is_null($endDate))
        { return $query; }

        if(!is_null($startDate) && is_null($endDate))
        { return $query->where('created_at', ">=", $startDate); }

        if(is_null($startDate) && !is_null($endDate))
        {
            $endDate1 = Carbon::parse($endDate)->addDays(1); 
            //$endDateは選んだ日付(1/1 00:00)になるので、1/1は入らない。
            //Carbon::parseで文字列でも日付の型として扱える。その後、addDaysで1/2 00:00にしてあげる
            return $query->where('created_at', '<=', $endDate1);
        }

        if(!is_null($startDate) && !is_null($endDate))
        {
            $endDate1 = Carbon::parse($endDate)->addDays(1);
            //$endDateは選んだ日付(1/1 00:00)になるので、1/1は入らない。
            //Carbon::parseで文字列でも日付の型として扱える。その後、addDaysで1/2 00:00にしてあげる

            return $query->where('created_at', ">=", $startDate)
            ->where('created_at', '<=', $endDate1);
        }
    }
}
