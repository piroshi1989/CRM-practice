<script setup>
import { Inertia } from '@inertiajs/inertia'

defineProps({
    id: String,
    blog: Object
    //1件だけ取得する際はArrayではなくObjectで。
})

const deleteConfirm = id => {
    Inertia.delete(`/inertia/${id}`, {
        onBefore: () => confirm('本当に削除しますか？')
    })
}
</script>

<template>
{{ id }}<br>
{{ blog.title }}<br>
<button @click="deleteConfirm(blog.id)">削除</button>
</template>