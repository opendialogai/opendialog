<template>
    <panel-item :field="field">
        <template slot="value">
            <div class="message mb-6" v-for="message in field.value">
                <template v-if="message.type == 'text-message'">
                    <div class="text-message" v-html="message.data"></div>
                </template>
                <template v-if="message.type == 'button-message'">
                    <div class="button-message">
                        <div v-html="message.data.text"></div>
                        <div class="buttons" v-for="button in message.data.buttons">
                            <button class="btn btn-default btn-primary mt-1 mr-2">{{ button.text }}</button>
                        </div>
                    </div>
                </template>
                <template v-if="message.type == 'image-message'">
                    <div class="image-message">
                        <template v-if="message.data.link">
                            <a :href="message.data.link">
                                <img :src="message.data.src" />
                            </a>
                        </template>
                        <template v-else>
                            <img :src="message.data.src" />
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </panel-item>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],
}
</script>

<style lang="scss" scoped>
.message {
    .text-message,
    .button-message,
    .image-message {
        border-radius: 6px;
        padding: 7px 10px;
        background: #eaeaea;
        max-width: 300px;
    }
}
</style>
