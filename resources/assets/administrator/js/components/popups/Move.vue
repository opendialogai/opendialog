<template>
    <div class="modal fade" id="move">
        <form @submit.prevent="$emit('move', target)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <modal-header text="Move"></modal-header>

                    <div class="modal-body">
                        <select class="form-control" v-model="target">
                            <option v-for="dir in directories" :value="filename(dir)">{{ filename(dir) }}</option>
                        </select>
                    </div>

                    <modal-footer></modal-footer>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        data() {
            return {
                target: '',
            };
        },
        props: {
            directories: {
                type: Array,
                default: () => [],
            },
        },
        methods: {
            filename(dir) {
                return ('../' === dir ? dir : dir.filename);
            },
        },
        mounted() {
            const first = (this.directories || [])[0];

            if (first) {
                this.target = this.filename(first);
            }
        },
    };
</script>
