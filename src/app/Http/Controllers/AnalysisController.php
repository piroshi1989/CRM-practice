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
        $startDate = '2021-08-01';
        $endDate = '2022-08-31';

        // $period = Order::betweenDate($startDate, $endDate)->groupBy('id') //購買ID毎にまとめる
        // ->selectRaw('id, sum(subtotal) as total, customer_name, status, created_at')
        // ->orderBy('created_at') //購入日で順番を並び替える
        // ->paginate(50);

        // dd($period);

        // //日別
        // $subQuery = Order::betweenDate($startDate, $endDate) //期間指定
        // ->where('status', true)->groupBy('id') //キャンセルされていないもののみ取得,購買毎
        // ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');
        // //小計をsumで合計, DATE_FORMAT...mysqlの関数

        // $data = DB::table($subQuery)
        // ->groupBy('date')
        // ->selectRaw('date, sum(totalPerPurchase) as total')->get();

        // dd($data);

        // dd($data);
        
        return Inertia::render('Analysis');
    }

    public function decile()
    {
        //期間指定
        $startDate = '2021-08-01';
        $endDate = '2022-08-31';
        
        // 1. 購買ID毎にまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)
        ->groupBy('id')
        ->selectRaw('id, customer_id, customer_name,
        SUM(subtotal) as totalPerPurchase');

        // 2. 会員毎にまとめて購入金額順にソートする
        $subQuery = DB::table($subQuery)
        ->groupBy('customer_id')
        ->selectRaw('customer_id, customer_name,
        sum(totalPerPurchase) as total')
        ->orderBy('total', 'desc');

        // dd($subQuery);

        // statementで変数を設定できる.値を返さないもの。
        // set @変数名 = 値 (mysqlの書き方)
        // 3. 購入順に連番を振る
        DB::statement('set @row_num = 0;');
        $subQuery = DB::table($subQuery)
        ->selectRaw('
        @row_num:= @row_num+1 as row_num,
        customer_id,
        customer_name,
        total');
        //sqlの結果を変数に格納したい場合は:=を利用

        // dd($subQuery);

        // 4. 全体の件数を数え、1/10の値や合計金額を取得
        $count = DB::table($subQuery)->count();
        $total = DB::table($subQuery)->selectRaw('sum(total) as total')->value('total'); // クエリを実行し、totalの値を取得
        $decile = ceil($count / 10); // 10分の1の件数を変数に入れる
        //ceil..繰り上げ

        $bindValues = [];
        $tempValue = 0;

        for($i = 1; $i <= 10; $i++)
        {
        array_push($bindValues, 1 + $tempValue);
        $tempValue += $decile;
        array_push($bindValues, 1 + $tempValue);
        }

        // dd($count, $decile, $bindValues);

        // 5 10分割しグループ毎に数字を振る
        DB::statement('set @row_num = 0;');
        $subQuery = DB::table($subQuery)
        ->selectRaw("
        row_num,
        customer_id,
        customer_name,
        total,
        case
        when ? <= row_num and row_num < ? then 1
        when ? <= row_num and row_num < ? then 2
        when ? <= row_num and row_num < ? then 3
        when ? <= row_num and row_num < ? then 4
        when ? <= row_num and row_num < ? then 5
        when ? <= row_num and row_num < ? then 6
        when ? <= row_num and row_num < ? then 7
        when ? <= row_num and row_num < ? then 8
        when ? <= row_num and row_num < ? then 9
        when ? <= row_num and row_num < ? then 10
        end as decile
        ", $bindValues); // SelectRaw第二引数にバインドしたい数値(配列)をいれる 
        //selectRawの第二引数に数値(配列)を渡すと？のところに数字が入る

        // dd($subQuery);

        // round, avg はmysqlの関数
        // 6. グループ毎の合計・平均
        $subQuery = DB::table($subQuery)
        ->groupBy('decile')
        ->selectRaw('decile,
        round(avg(total)) as average,
        sum(total) as totalPerGroup');
        //round..四捨五入

        // dd($subQuery);

        // 7 構成比
        DB::statement("set @total = {$total} ;");
        $data = DB::table($subQuery)
        ->selectRaw('decile,
        average,
        totalPerGroup,
        round(100 * totalPerGroup / @total, 1) as
        totalRatio
        ')->get();
    }
}
