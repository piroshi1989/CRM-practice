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
        $startDate = '2023-12-20';
        $endDate = '2024-01-03';

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

         //RFM分析
        

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

    public function rfm()
    {
        $startDate = '2023-01-01';
        $endDate = '2024-01-03';
        
        // 1. 購買ID毎にまとめる
        $subQuery = Order::betweenDate($startDate,
        $endDate)
        ->groupBy('id')
        ->selectRaw('id, customer_id,
        customer_name, SUM(subtotal) as
        totalPerPurchase, created_at');
        
        //max...sqlの関数。maxをかけて、最新の日を取得
        //datediff...日付の比較
        // datediffで日付の差分, maxで日付の最新日
        // 2. 会員毎にまとめて最終購入日、回数、合計金額を取得
        $subQuery = DB::table($subQuery)
        ->groupBy('customer_id')
        ->selectRaw('customer_id, customer_name,
        max(created_at) as recentDate,
        datediff(now(), max(created_at)) as recency,
        count(customer_id) as frequency,
        sum(totalPerPurchase) as monetary');

        // dd($subQuery);

        // 4. 会員毎のRFMランクを計算
        $rfmPrms = [
            14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000,30000 ];

        $subQuery = DB::table($subQuery)
        ->selectRaw('customer_id, customer_name,
        recentDate, recency, frequency, monetary,
        case
        when recency < ? then 5
        when recency < ? then 4
        when recency < ? then 3
        when recency < ? then 2
        else 1 end as r,
        case
        when ? <= frequency then 5
        when ? <= frequency then 4
        when ? <= frequency then 3
        when ? <= frequency then 2
        else 1 end as f,
        case
        when ? <= monetary then 5
        when ? <= monetary then 4
        when ? <= monetary then 3
        when ? <= monetary then 2
        else 1 end as m', $rfmPrms);

        // dd($subQuery->get());

        // rightJoin...後ろからくっつける


        // 5.ランク毎の数を計算する
        $total = DB::table($subQuery)->count();

        $rCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=', 'r')
        //第一引数はテーブル名 rankとrを紐づける
        //これをすると検索期間が短く、rが5種類ないときでもnullになる(errorにならない)
        ->selectRaw('ranks.rank as r, count(r)')
        ->groupBy('ranks.rank')
        ->orderBy('r', 'desc')
        ->pluck('count(r)'); //ランクごとの人数が欲しい

        $fCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=', 'f')
        ->selectRaw('ranks.rank as f, count(f)')
        ->groupBy('ranks.rank')
        ->orderBy('f', 'desc')
        ->pluck('count(f)');

        $mCount = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=', 'm')
        ->selectRaw('ranks.rank as m, count(m)')
        ->groupBy('ranks.rank')
        ->orderBy('m', 'desc')
        ->pluck('count(m)');

        $eachCount = []; // Vue側に渡すようの空の配列
        $rank = 5; // 初期値5
        for($i = 0; $i < 5; $i++)
        {
            array_push($eachCount, [
                'rank' => $rank,
                'r' => $rCount[$i],
                'f' => $fCount[$i],
                'm' => $mCount[$i],
            ]);
            $rank--; // rankを1ずつ減らす
        }

        // dd($total, $eachCount, $rCount, $fCount, $mCount);

        // concatで文字列結合...rは5,4,3,2,1で定義済
        // 6. RとFで2次元で表示してみる
        $data = DB::table($subQuery)
        ->rightJoin('ranks', 'ranks.rank', '=', 'r')
        ->selectRaw('concat("r_", ranks.rank) as rRank,
        count(case when f = 5 then 1 end ) as f_5,
        count(case when f = 4 then 1 end ) as f_4,
        count(case when f = 3 then 1 end ) as f_3,
        count(case when f = 2 then 1 end ) as f_2,
        count(case when f = 1 then 1 end ) as f_1')
        ->groupBy('ranks.rank')
        ->orderBy('rRank', 'desc')
        ->get();

        //  dd($data);
    }
}