<template>
    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
        <span v-if="responseMessage"
              :class="{'text-green': responseOk, 'text-red': !responseOk}"
              v-html="responseMessage"
              class="media-list__inline-response"></span>
        <button class="btn btn-primary">
            <i class="fa fa-save"></i>&nbsp;Save
        </button>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        computed: Object.assign(
            {},
            mapGetters('response', {
                responseOk: 'success',
                responseMessage: 'message',
            }),
        ),
    };
</script>

<style>
    .media-list__inline-response {
        margin-right: 10px;
    }
    .media-list__inline-response.text-red {
        color: red;
    }
    .media-list__inline-response.text-green {
        color: green;
    }
</style>