<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia'
import { getToday } from '@/common.js'
import { onMounted, reactive, ref, computed } from 'vue'


const props = defineProps({
    items: Array,
    customers: Array
})

const itemList = ref([])
//propsのままだと情報を変更できないためreactiveな配列を作って追加する
//配列のときはrefを使用

const quantity = [ "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"] // option用

const form = reactive({
    date: null,
    customer_id: null,
    status: true,
    items: []
})

onMounted(() => {
    form.date = getToday();
    props.items.forEach( item  => {
        itemList.value.push({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: 0
        })
    })
})
//forEach..1件ずつ処理を実行する
//ページを読み込んだら関数が実行されるようにする


const totalPrice = computed(() => {
    let total = 0
    itemList.value.forEach( item => {
    total += item.price * item.quantity
    })
    return total
})
//変更があり次第再計算する(監視している)
//computedはreturnが必要

const storePurchase = () => {
    itemList.value.forEach( item => {
        if( item.quantity > 0){
            form.items.push({
                id: item.id,
                quantity: item.quantity
            })
        }
    })
    Inertia.post(route('purchases.store', form))
}
</script>

<template>
    <form @submit.prevent="storePurchase">
    日付<br>
    <input type="date" name="date" v-model="form.date"><br>
    会員名<br>
    <!-- 1人顧客を入れると、フォームにカスタムIDが入るようにするためv-modelで設定 -->
    
    <select name="customer" v-model="form.customer_id">
        <option v-for="customer in customers" :value="customer.id" :key="customer.id">
        {{ customer.id }} : {{ customer.name }}
        </option>
    </select>
    {{ form.customer_id }}

<br><br>
商品・サービス<br>
<table>
 <thead>
    <tr>
        <th>Id</th>
        <th>商品名</th>
        <th>金額</th>
        <th>数量</th>
        <th>小計</th>
    </tr>
 </thead>
 <tbody>
    <tr v-for="item in itemList" >
        <td>{{ item.id }}</td>
        <td>{{ item.name }}</td>
        <td>{{ item.price }}</td>
        <td>
        <select name="quantity" v-model="item.quantity">
        <option v-for="q in quantity" :value="q">{{ q }}</option>
        </select>
        </td>
    </tr>
 </tbody>
</table>

<br>
合計: {{ totalPrice }}円<br>
<button>登録する</button>
</form>
</template>