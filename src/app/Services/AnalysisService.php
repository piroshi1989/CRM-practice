<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
class AnalysisService
{
    //fatControllerになってきたのでserviceを作成して避ける

    public static function perDay($subQuery)
    {
        $query = $subQuery->where('status', true)->groupBy('id') //キャンセルされていないもののみ取得,購買毎
        ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');
        //小計をsumで合計, DATE_FORMAT...mysqlの関数
        
        //日別の合計
        $data = DB::table($query)
        ->groupBy('date')
        ->selectRaw('date, sum(totalPerPurchase) as total')->get();

        $labels = $data->pluck('date');
        $totals = $data->pluck('total');

        //複数の変数を渡すため変数を配列に入れる
        return [$data, $labels, $totals];
    }

    public static function perMonth($subQuery) //DATE_FORMATを変更する
    {
        $query = $subQuery->where('status', true)->groupBy('id') //キャンセルされていないもののみ取得,購買毎
        ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m") as date');
        //小計をsumで合計, DATE_FORMAT...mysqlの関数
        
        //月別の合計
        $data = DB::table($query)
        ->groupBy('date')
        ->selectRaw('date, sum(totalPerPurchase) as total')->get();

        $labels = $data->pluck('date');
        $totals = $data->pluck('total');

        //複数の変数を渡すため変数を配列に入れる
        return [$data, $labels, $totals];
    }

    public static function perYear($subQuery)//DATE_FORMATを変更する
    {
        $query = $subQuery->where('status', true)->groupBy('id') //キャンセルされていないもののみ取得,購買毎
        ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y") as date');
        //小計をsumで合計, DATE_FORMAT...mysqlの関数
        
        //年別の合計
        $data = DB::table($query)
        ->groupBy('date')
        ->selectRaw('date, sum(totalPerPurchase) as total')->get();

        $labels = $data->pluck('date');
        $totals = $data->pluck('total');

        //複数の変数を渡すため変数を配列に入れる
        return [$data, $labels, $totals];
    }
}