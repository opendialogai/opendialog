<template>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="close">
            <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title">{{ text }}</h4>
    </div>
</template>

<script>
    export default {
        props: ['text'],
    };
</script>