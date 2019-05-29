<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">File info</h3>
        </div>

        <div class="panel-body">
            <div style="display:flex; flex-direction: row; justify-content: center; align-items: flex-start;">
                <img class="img-responsive" :src="file('url')" v-if="file('isImage')"/>
            </div>

            <h5 class="profile-username text-center">
                <div v-if="!file('isImage')">
                    <i :class="file('icon')" style="font-size: 96px;"></i>
                    <br/><br/>
                </div>
                {{ file('basename') }}
            </h5>

            <ul class="list-group list-group-unbordered media-file-info">
                <li class="list-group-item">
                    <b>Size</b>
                    <span class="pull-right">
                        {{ file('size') }} Bytes
                    </span>
                </li>
                <li class="list-group-item">
                    <b>Created</b><br/>
                    {{ file('createdAt') }}
                </li>
                <li class="list-group-item">
                    <b>Updated</b><br/>
                    {{ file('updatedAt') }}
                </li>
            </ul>
            <a target="_blank" class="btn btn-primary btn-block" :href="file('url')">
                Preview
            </a>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['file']
    };
</script>