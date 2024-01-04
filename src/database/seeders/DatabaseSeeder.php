<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Item;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ItemSeeder::class,
        ]);
        \App\Models\Customer::factory(1000)->create();

        $items = Item::all();

        Purchase::factory(30000)->create()
        ->each(function(Purchase $purchase) use ($items){
            //useをつけることで関数の外側で定義した変数を使用することができる
        $purchase->Items()->attach(
        $items->random(rand(1,3))->pluck('id')->toArray(),
        // 1～3個のitemをpurchaseにランダムに紐づけ
        ['quantity' => rand(1, 5) ] );
        });
        //each,,,1件ずつ処理
        //attach,,,中間テーブルに同時に情報登録
        //外部キー以外で中間テーブルに情報追加するには第二引数に書く

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
