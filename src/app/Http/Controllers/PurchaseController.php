<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\Item;
use App\Models\Customer;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(Order::paginate(50));
        //大量データを表示させたいときはallとかgetを使うと重すぎる
        //paginateやchunkを使用する

        $orders = Order::groupBy('id')
        ->selectRaw('id, sum(subtotal) as total,
        customer_name, status, created_at')
        ->paginate(50);

        // dd($orders);

        return Inertia::render('Purchases/Index', [
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $customers = customer::select('id', 'name', 'kana')->get();
        $items = Item::select('id', 'name', 'price')->where('is_selling', true)->get();

        return Inertia::render('Purchases/Create', [
            // 'customers' => $customers,
            'items' => $items
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePurchaseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseRequest $request)
    {
        // dd($request);
        //中間テーブル、purchaseテーブル2つに保存するため、どちらか保存に失敗したら戻せるようトランザクションを書く
        //ドキュメント参照
        DB::beginTransaction();

        try{
            $purchase = Purchase::create([
                'customer_id' => $request->customer_id,
                'status' => $request->status,
            ]);
    
            foreach($request->items as $item){
                $purchase->items()->attach($purchase->id, [
                    'item_id' => $item['id'],
                    'quantity' => $item['quantity']
                ]);
            }
            //attachで中間テーブルにも値を渡す。第一引数は紐づくデータベーステーブルのID,第二に連想配列
    
            DB::commit();

            return to_route('dashboard');

        }catch(\Exception $e){
            DB::rollback();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {
        //小計
        $items = Order::where('id', $purchase->id)->get();

        //合計
        $orders = Order::groupBy('id')
        ->where('id', $purchase->id)
        ->selectRaw('id, sum(subtotal) as total,
        customer_name, status, created_at')
        ->get();

        // dd($items, $orders);

        return Inertia::render('Purchases/Show', [
            'items' => $items,
            'order' => $orders
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $purchase = Purchase::find($purchase->id);

        $allItems = Item::select('id', 'name', 'price')
        ->get();

        $items = [];
        //初期値

        //1件ずつのitemのidを取得する
        foreach($allItems as $allItem){
            $quantity = 0;  //初期値0 中間テーブルの情報があれば更新していく
            foreach($purchase->items as $item){  //中間テーブルに入っているものを1件ずつ取得
                if($allItem->id === $item->id){ //中間テーブルにIDが存在していたら処理する
                    $quantity = $item->pivot->quantity; //中間テーブルの数量の情報を取得する
                }
            }
            array_push($items, [
                'id' => $allItem->id,
                'name' => $allItem->name,
                'price' => $allItem->price,
                'quantity' => $quantity //中間テーブルに数量があれば値が入り、なければ0のまま
            ]);
        }
        // dd($items);

        //合計
        $order = Order::groupBy('id')
        ->where('id', $purchase->id)
        ->selectRaw('id, customer_id,
        customer_name, status, created_at')
        ->get();

        return Inertia::render('Purchases/Edit', [
            'items' => $items,
            'order' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePurchaseRequest  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        // dd($request, $purchase);
        
        //2つのテーブルを更新するのでトランザクションをかける
        DB::beginTransaction();

        try{
        $purchase->status = $request->status;
        $purchase->save();

        $items = [];

        foreach($request->items as $item){
            $items = $items + [
                $item['id'] => [
                    'quantity' => $item['quantity']
                ]
            ];
        }

        $purchase->items()->sync($item);
        //中間テーブルの情報を更新するにはsync()が便利
        //引数に配列が必要

        DB::commit();
        
        return to_route('dashboard');
        } catch(\Exception $e){
            DB::rollback();
}
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
