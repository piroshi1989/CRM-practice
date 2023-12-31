<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import { Inertia } from '@inertiajs/inertia';
import { ref } from 'vue'


const props = defineProps({
    customers: Object
    //paginateで取得したらArrayではなくObjectになる
})
const searchText = sessionStorage.getItem('searchKey');

const search = ref(searchText)
//文字列、数値などのプリミティブな値の場合はref

const searchCustomers = () => {
    sessionStorage.setItem('searchKey' , search.value );
    Inertia.get(route('customers.index', { search: search.value}))
    //refの値を取得するには.valueが必要
    //sessionを使用することで検索のinputにwordが残るようになる
}

</script>

<template>
     <Head title="顧客一覧" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                顧客一覧
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <section class="text-gray-600 body-font">
                            <FlashMessage />
                            <div class="container px-5 py-8 mx-auto">
                                <div>
                                    <input type="text" name="search" v-model="search">
                                    <button class="bg-blue-300 text-white py-2 px-2"
                                    @click="searchCustomers">検索</button>
                                </div>
                                <div class="flex pl-4 my-4 lg:w-2/3 w-full mx-auto">
                                    <Link as="button" :href="route('customers.create')"
                                    class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">
                                    顧客登録
                                    </Link>
                                </div>
                                <div class="lg:w-2/3 w-full mx-auto overflow-auto">
                                <table class="table-auto w-full text-left whitespace-no-wrap">
                                    <thead>
                                    <tr>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">Id</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">氏名</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">カナ</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">電話番号</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="customer in customers.data" :key="customer.id">
                                        <td class="border-b-2 border-gray-200 px-4 py-3">
                                            <Link class="text-blue-400" :href="route('customers.show', {customer: customer.id})">{{ customer.id }}</Link></td>
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ customer.name }}</td>
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ customer.kana }}</td>
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ customer.tel }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                            <Pagination class="mt-6" :links="customers.links"></Pagination>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
