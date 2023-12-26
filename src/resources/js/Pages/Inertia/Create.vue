<script setup>
import { reactive } from 'vue'
import { Inertia } from '@inertiajs/inertia'

defineProps({
    errors: Object
})

const form = reactive({
    title: null,
    content: null
    // 初期値は空
})

const submitFunction = () => {
    Inertia.post('/inertia', form)
    // 第二引数で渡したい値
}
// リアクティブでオブジェクトはreactiveを使用する

</script>

<template>
    <form @submit.prevent="submitFunction">
        <input type="text" name="title" v-model="form.title"><br>
        <div v-if="errors.title">{{ errors.title }}</div><br>
        <input type="text" name="content" v-model="form.content"><br>
        <div v-if="errors.content">{{ errors.content }}</div><br>
        <button>送信</button>
    </form>
</template>