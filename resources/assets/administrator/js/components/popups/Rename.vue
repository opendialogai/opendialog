<template>
    <div class="modal fade" id="rename">
        <form @submit.prevent="rename()">
            <div class="modal-dialog">
                <div class="modal-content">
                    <modal-header text="Rename"></modal-header>

                    <div class="modal-body">
                        <input required class="form-control" v-model="name"/>
                    </div>

                    <modal-footer></modal-footer>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        data() {
            return {name: ''};
        },

        methods: {
            rename() {
                const from = this.$store.getters['selection/all'][0].basename;
                this.$emit('rename', {
                    from,
                    to: this.name,
                });
            },
        },

        mounted() {
            const selected = this.$store.getters['selection/all'];

            this.name = selected.length ? selected[0].basename : '';
        },
    };
</script>
