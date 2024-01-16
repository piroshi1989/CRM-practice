<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use App\Services\AnalysisService;
use App\Services\DecileService;
use App\Services\RFMService;
//fatControllerになってきたのでserviceを作成して避ける

class AnalysisController extends Controller
{
    public function index(Request $request)
    {
        $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if($request->type === 'perDay')//日別だったら、
        {
            // 配列を受け取り変数に格納するため list() を使う
            list($data, $labels, $totals) = AnalysisService::perDay($subQuery);
        }

        if($request->type === 'perMonth')//月別だったら、
        {
            // 配列を受け取り変数に格納するため list() を使う
            list($data, $labels, $totals) = AnalysisService::perMonth($subQuery);
        }

        if($request->type === 'perYear')//年別だったら、
        {
            // 配列を受け取り変数に格納するため list() を使う
            list($data, $labels, $totals) = AnalysisService::perYear($subQuery);
        }

        if($request->type === 'decile')//デシルだったら、
        {
            // 配列を受け取り変数に格納するため list() を使う
            list($data, $labels, $totals) = DecileService::decile($subQuery);
        }

        if($request->type === 'rfm')//RFMだったら、
        {
            // 配列を受け取り変数に格納するため list() を使う
            list($data, $totals, $eachCount) = RFMService::frm($subQuery, $request->rfmPrms);
            //配列を渡す必要があるのでここでは$request->rfmPrmsを追記
        
            //Ajax通信なのでJson形式で返却する必要がある
            return response()->json([
                'data' => $data,
                'type' => $request->type,
                'totals' => $totals,
                'eachCount' => $eachCount,

            ], Response::HTTP_OK);
            // Response::HTTP_OK  定数
        }

        //Ajax通信なのでJson形式で返却する必要がある
        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals,

        ], Response::HTTP_OK);
        // Response::HTTP_OK  定数
    }
}
