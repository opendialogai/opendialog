<template>
    <div>
        <div class="well well-asset-options">
            <file-actions :num-selected="selectedCount" @remove="remove"></file-actions>
        </div>
        <div class="row">
            <div class="col-xs-9">
                <files-list :collection="files" :modal="isModal" @selected="onMediaSelected"></files-list>
            </div>

            <div class="col-xs-3">
                <file-info v-if="1 === selectedCount" :file="fileInfo"></file-info>

                <folders-list :collection="folders" @cd="chDir" @remove="remove"></folders-list>

                <drop-zone @upload-complete="uploadComplete"></drop-zone>
            </div>
        </div>

        <make-dir-popup @mkdir="mkDir"></make-dir-popup>
        <move-popup v-if="selectedCount" :directories="folders" @move="move"></move-popup>
        <rename-popup v-if="1 === selectedCount" @rename="rename"></rename-popup>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import Api from '../media/Api';

    export default {
        computed: Object.assign(
            mapGetters({
                selectedCount: 'selection/count',
                files: 'storage/files',
                folders: 'storage/directories',
                fileInfo: 'selection/info',
            }), {
                isModal() {
                    return !!window.mediaPopup || false;
                },
            },
        ),

        methods: {
            remove(object) {
                if (!window.confirm('Danger! Are you sure?')) {
                    return false;
                }

                return Api.remove(object || null).then(() => {
                    this.$store.dispatch('selection/clean');
                });
            },

            move(destination) {
                return Api.move(this.$store.getters['selection/all'], destination).then(response => {
                    if (this.$store.getters['response/success']) {
                        this.$store.dispatch('storage/remove', this.$store.getters['selection/all']);
                        this.$store.dispatch('selection/clean');

                        $('#move').modal('toggle');
                    }
                });
            },

            rename(payload) {
                const source = payload.from,
                    destination = payload.to;

                if (source === destination) {
                    return;
                }

                return Api.rename(source, destination).then((response) => {
                    this.$store.dispatch('selection/clean');
                    this.$store.dispatch('storage/rename', {
                        from: source,
                        to: response.data.file,
                    });
                    $('#rename').modal('toggle');
                });
            },

            mkDir(name) {
                return Api.mkDir(name).then(response => {
                    if (this.$store.getters['response/success']) {
                        this.$store.dispatch('storage/push', response.data.data);
                    }

                    return response;
                });
            },

            chDir(file) {
                if ('../' === file) {
                    let path = window.REQUEST_PATH.split('/');
                    path = path.splice(0, path.length - 1).join('/');
                    window.location.href = '?path=' + path;

                    return false;
                }

                if (!!file.isDir) {
                    window.location.href = '?path=' + file.path;

                    return false;
                }

                return false;
            },

            uploadComplete(file) {
                this.$store.dispatch('storage/push', file);
            },

            onMediaSelected(file) {
                window.opener.onMediaFileSelected(file.url);
            },
        },
    };
</script>