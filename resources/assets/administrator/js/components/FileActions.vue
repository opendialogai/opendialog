<template>
    <div class="btn-toolbar btn-toolbar-media-manager">
        <div class="box-tools">
            <div class="btn-group">
                <button type="button" class="btn btn-default" @click="openFileRequest">
                    <i class="fa fa-upload"></i>&nbsp;Upload
                </button>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#mkdir">
                    <i class="fa fa-folder"></i>&nbsp;Make Directory
                </button>
            </div>
            <div class="btn-group ml10 sep-prefixed" v-if="numSelected">
                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#move">
                    <i class="fa fa-share"></i>&nbsp;Move
                </button>

                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#rename" v-if="1 === numSelected">
                    <i class="fa fa-i-cursor"></i>&nbsp;Rename
                </button>

                <button type="button" class="btn btn-danger" @click="$emit('remove')">
                    <i class="fa fa-trash"></i>&nbsp;Delete
                </button>
            </div>
        </div>
    </div>
</template>

<style>
    .sep-prefixed {
        padding-left: 10px;
        border-left: 1px solid #818181;
    }
</style>

<script>
    export default {
        props: {
            numSelected: {
                type: Number,
                default: 0,
            },
        },

        methods: {
            openFileRequest() {
                $('.dz-clickable:eq(0)').click();
            }
        }
    };
</script>