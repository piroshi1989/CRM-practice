<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if($request->type === 'perDay')//日別だったら、
        {
            $subQuery->where('status', true)->groupBy('id') //キャンセルされていないもののみ取得,購買毎
            ->selectRaw('id, SUM(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');
            //小計をsumで合計, DATE_FORMAT...mysqlの関数
            
            //日別の合計
            $data = DB::table($subQuery)
            ->groupBy('date')
            ->selectRaw('date, sum(totalPerPurchase) as total')->get();

            $labels = $data->pluck('date');
            $totals = $data->pluck('total');
        }
        //Ajax通信なのでJson形式で返却する必要がある
        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals
        ], Response::HTTP_OK);
        // Response::HTTP_OK  定数
    }
}
