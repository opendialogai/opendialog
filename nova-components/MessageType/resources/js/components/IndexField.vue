<template>
    <span>{{ field.value }}</span>
</template>

<script>
export default {
    props: ['resourceName', 'field'],

    mounted() {
        if (this.field.value == 'user') {
            this.$parent.$el.classList.add('message', 'user');
        }
        else if (this.field.value == 'chatbot') {
            this.$parent.$el.classList.add('message', 'chatbot');
        }
    }
}
</script>
