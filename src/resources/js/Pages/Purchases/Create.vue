<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia'
import { getToday } from '@/common.js'
import { onMounted, reactive, ref, computed } from 'vue'
import MicroModal from '@/Components/MicroModal.vue'

const props = defineProps({
    items: Array
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

const setCustomerId = id => {
    form.customer_id = id
    //formに入れるとデータベースに入れることができる
}
//子供からidがわたってくる
</script>

<template>
    <Head title="購入画面" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">購入画面</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <section class="text-gray-600 body-font relative">
                            <form @submit.prevent="storePurchase">
                                <div class="container px-5 py-8 mx-auto">
                                    <div class="lg:w-1/2 md:w-2/3 mx-auto">
                                        <div class="flex flex-wrap -m-2">
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="date" class="leading-7 text-sm text-gray-600">日付</label>
                                                    <input type="date" id="date" name="date" v-model="form.date" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <!-- <div v-if="errors.date">{{ errors.date }}</div><br> -->
                                                </div>
                                            </div>
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="customer" class="leading-7 text-sm text-gray-600">会員名</label>
                                                    <MicroModal @update:customerId="setCustomerId"/>
                                                    <!-- <div v-if="errors.customer">{{ errors.customer }}</div><br> -->
                                                </div>
                                            </div>
                                            <div class="w-full mt-8 mx-auto overflow-auto">
                                <table class="table-auto w-full text-left whitespace-no-wrap">
                                    <thead>
                                    <tr>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">Id</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">商品名</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">金額</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">数量</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">小計</th>
                                       
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="item in itemList" :key="item.id">
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.id }}</td>
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.name }}</td>
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.price }}</td>
                                        <td class="border-b-2 border-gray-200 px-4 py-3">
                                            <select name="quantity" v-model="item.quantity">
                                            <option v-for="q in quantity" :value="q">{{ q }}</option>
                                            </select>
                                        </td>
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.price * item.quantity }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                                            </div>
                                            <div class="p-2 w-full">
                                                <div class="">
                                                    <label for="price" class="leading-7 text-sm text-gray-600">合計金額</label><br>
                                                    <div class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <!-- <div v-if="errors.date">{{ errors.date }}</div><br> -->
                                                    {{ totalPrice }}円
                                                </div>
                                                </div>
                                            </div>
                                            <div class="p-2 w-full">
                                                <button class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
