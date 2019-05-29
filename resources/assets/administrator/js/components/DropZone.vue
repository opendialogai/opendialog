<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Drop zone</h3>
        </div>
        <div class="panel-body">
            <div class="text-center" id="dropzone-container">
                <vue-clip :options="options" :on-complete="onComplete" :on-init="onInit">
                    <template slot="clip-uploader-action" slot-scope="state">
                        <div class="media-drop-zone progress" :class="{'drag-over': state.dragging}">Click or Drag and Drop files here upload.</div>
                    </template>

                    <template slot="clip-uploader-body" slot-scope="state">
                        <ul class="list-unstyled">
                            <li v-for="file in state.files">
                                <div class="text-left queue-item">
                                    <div class="file-properties">
                                        <div v-if="file.dataUrl" class="clip-preview" :style="{backgroundImage: 'url(' + file.dataUrl + ')'}"></div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-green" role="progressbar" aria-valuemin="0" aria-valuemax="100" :style="{width: file.progress + '%'}"></div>
                                        </div>
                                        <a v-if="['added', 'error'].indexOf(file.status) !== -1" class="pull-right" @click="unQueue(file)"><i class="fa fa-trash"></i></a>

                                        <strong :class="{'text-danger': 'error' === file.status}">{{ file.name | truncate(25) }}</strong>
                                        <p v-if="file.errorMessage.length" v-text="file.errorMessage"></p>
                                    </div>
                                </div>
                            </li>
                        </ul>

                        <button v-if="state.files.length" class="btn btn-primary btn-block" @click="processQueue()">Upload</button>
                    </template>
                </vue-clip>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            let token = document.head.querySelector('meta[name="csrf-token"]');

            return {
                clip: null,
                dropZone: null,
                options: {
                    url: this.uploadUrl(),
                    headers: {
                        'X-CSRF-TOKEN': token.content || '',
                    },
                    autoProcessQueue: false,
                    parallelUploads: 5,
                    /**
                     * Images preview.
                     */
                    createImageThumbnails: false,
                },
            };
        },

        methods: {
            uploadUrl(path) {
                const url = window.UPLOADER_URL;
                const params = $.param({path: (path && path.length) ? path : window.REQUEST_PATH});

                return url + '?' + params;
            },

            onInit(vueClip) {
                this.clip = vueClip;
                this.dropZone = vueClip.uploader._uploader;
            },

            onComplete(file, status, xhr) {
                if ('success' === status) {
                    setTimeout(() => this.unQueue(file), 500);

                    const response = JSON.parse(xhr.response);

                    this.$emit('upload-complete', response.file);
                }
            },

            processQueue() {
                this.dropZone.processQueue();
            },

            unQueue(file) {
                this.clip.files.splice(this.clip.files.indexOf(file), 1);
            },
        },
    };
</script>

<style scoped>
    .media-drop-zone.drag-over {
        border: 3px dashed #919191;
    }

    .media-drop-zone:hover {
        text-decoration: underline;
        cursor: pointer;
    }

    .clip-preview {
        width: 100%;
        height: 120px;
        -webkit-background-size: cover;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
    }
</style>
