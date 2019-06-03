<template>
    <div class="media-carousel">
        <p v-if="enabled && !readonly">
            <input class="drop-zone__file" type="file" :accept="acceptedTypes" :name="'_media_[' + id + '][]'" :ref="ref" @change="onFilesSelected" multiple/>
        </p>
        <input v-for="media in pendingRemoval" type="hidden" name="_media_[_trash_][]" :value="media">

        <div style="position: relative">
            <div v-if="!readonly && (editable || !media.length)"
                 class="drop-zone__area"
                 :class="{'placeholder': (!media.length && !editable)}"
                 @dragover.stop.prevent.self=""
                 @dragleave.stop.prevent.self="onDragLeave"
                 @drop.stop.prevent.self="addToQueue"
                 @dragenter.stop.prevent.self="onDragEnter"
            >
                Drop files here
            </div>

            <div :id="'media-' + id"
                 class="carousel slide"
                 data-ride="carousel"
                 :data-interval="interval"
                 @dragover.stop.prevent=""
                 @dragenter.stop.prevent="onDragEnter"
            >
                <!-- Indicators -->
                <ol class="carousel-indicators" v-if="media.length > 1 && hasIndicators">
                    <li v-for="(item, index) in media"
                        :data-target="'#media-' + id"
                        :data-slide-to="index"
                        :class="{'active': active === index}"
                        @click.prevent="setActive(index)"
                    ></li>
                </ol>

                <!-- Wrapper for slides -->
                <div class="carousel-inner">
                    <div class="item active" v-if="!media.length">
                        <img src="/images/vendor/fancybox/dist/blank.gif" style="width: 320px; height: 160px;" alt="">
                    </div>
                    <div v-for="(item, index) in media" class="item" :class="{'active': active === index, 'removed': pending = pendingForRemoval(item)}">
                        <a class="media-carousel__toolbar" v-if="!readonly">
                            <i v-if="!pending" @click.prevent="removeMedia(item)" class="media-carousel__toolbar__action fa fa-trash"></i>
                            <i v-else @click.prevent="restoreMedia(item)" class="media-carousel__toolbar__action fa fa-refresh"></i>
                        </a>
                        <img :src="mediaUrl(item)" alt="">
                    </div>
                </div>

                <div v-if="media.length > 1 && hasArrows">
                    <a class="left carousel-control" :href="'#media-' + id" data-slide="prev">
                        <span class="glyphicon glyphicon-chevron-left"></span>
                        <span class="sr-only">&laquo;</span>
                    </a>
                    <a class="right carousel-control" :href="'#media-' + id" data-slide="next">
                        <span class="glyphicon glyphicon-chevron-right"></span>
                        <span class="sr-only">&raquo;</span>
                    </a>
                </div>
            </div>
        </div>

        <ul v-if="pendingRemoval.length" class="drop-zone__queue">
            <li v-text="pendingCount"></li>
        </ul>

        <ul v-if="queue.length" class="drop-zone__queue">
            <li>
                <a class="pull-right" @click.prevent="clearQueue"><i class="fa fa-trash"></i></a>
                <strong>Upload queue:</strong>
                <div class="clearfix"></div>
            </li>
            <li v-for="file in queue" v-text="file.name"></li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            id: {
                type: String,
            },
            media: {
                type: Array,
                default: [],
            },
            hasArrows: {
                type: Boolean,
                default: false,
            },
            hasIndicators: {
                type: Boolean,
                default: false,
            },
            conversion: {
                type: String,
                default: '',
            },
            interval: {
                type: Number,
                default: 0,
            },
            readonly: {
                type: Boolean,
                default: false,
            },
        },

        data() {
            return {
                editable: false,
                queue: [],
                pendingRemoval: [],
                enabled: true,
                active: 0,
            };
        },

        computed: {
            ref() {
                return 'scaffold_' + this.id;
            },

            acceptedTypes() {
                return (this.mimeTypes || []).join(', ');
            },

            pendingCount() {
                return `${this.pendingRemoval.length} item(s) queued for removal.`;
            },
        },

        methods: {
            pendingForRemoval(item) {
                return -1 !== this.pendingRemoval.indexOf(
                    parseInt(item.id),
                );
            },

            addToQueue(event) {
                this.input().files = event.dataTransfer.files;

                this.editable = false;
            },

            clearQueue() {
                if (!confirm('Are you sure?')) {
                    return false;
                }

                this.enabled = false;

                this.$nextTick(() => {
                    this.enabled = true;
                    this.queue = [];
                });
            },

            onDragEnter() {
                this.editable = true;
            },

            onDragLeave() {
                this.editable = false;
            },

            mediaUrl(item) {
                return this.conversion ? item.conversions[this.conversion] : item.url;
            },

            removeMedia(item) {
                this.pendingRemoval.push(
                    parseInt(item.id),
                );
            },

            restoreMedia(item) {
                this.pendingRemoval.splice(
                    this.pendingRemoval.indexOf(item.id),
                    1,
                );
            },

            onFilesSelected(event) {
                this.queue = event.target.files;
            },

            input() {
                return this.$refs[this.ref];
            },

            setActive(index) {
                setTimeout(() => this.active = index, 500);
            },
        },
    };
</script>

<style>
    .media-carousel__toolbar {
        opacity: 0;
        padding: 10px;
        width: 100%;
        background-color: rgba(100, 100, 100, 0.7);
        position: absolute;
        text-align: right;
    }

    .item:hover .media-carousel__toolbar {
        -webkit-transition: opacity 0.25s ease-in-out;
        -moz-transition: opacity 0.25s ease-in-out;
        transition: opacity 0.25s ease-in-out;
        opacity: 1;
        display: block;
    }

    .media-carousel__toolbar__action {
        cursor: pointer;
        color: white;
        opacity: 1;
    }

    .drop-zone__area {
        position: absolute;
        background: black;
        color: white;
        font-size: 1.2em;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-height: 100%;
        width: 100%;
        opacity: 0.8;
        z-index: 100;
    }

    .drop-zone__area.placeholder {
        opacity: 0.3;
    }

    .drop-zone__queue {
        margin: 0;
        padding: 0;
        width: 100%;
    }

    .drop-zone__queue li {
        text-align: left;
        list-style: none;
        padding: 8px;
        border-bottom: 1px solid #ccc;
    }

    .drop-zone__queue li:last-child {
        border-bottom: none;
    }

    .removed {
        opacity: 0.3;
    }
</style>