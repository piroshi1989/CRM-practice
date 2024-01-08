<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\DB;


class AnalysisController extends Controller
{
    public function index()
    {
        //期間指定
        $startDate = '2022-08-01';
        $endDate = '2022-08-31';

        // $period = Order::betweenDate($startDate, $endDate)->groupBy('id') //購買ID毎にまとめる
        // ->selectRaw('id, sum(subtotal) as total, customer_name, status, created_at')
        // ->orderBy('created_at') //購入日で順番を並び替える
        // ->paginate(50);

        // dd($period);

        //日別
        $subQuery = Order::betweenDate($startDate, $endDate) //期間指定
        ->where('status', true)->groupBy('id') //キャンセルされていないもののみ取得,購買毎
        ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');
        //小計をsumで合計, DATE_FORMAT...mysqlの関数

        $data = DB::table($subQuery)
        ->groupBy('date')
        ->selectRaw('date, sum(totalPerPurchase) as total')->get();

        // dd($data);

        return Inertia::render('Analysis');
    }
}
