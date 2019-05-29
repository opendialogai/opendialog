import store from './store/index';
import $http from './axios';

const Api = {
    basedir: window.REQUEST_PATH || '',

    /**
     * Create a new directory.
     *
     * @param name
     * @return Promise
     */
    mkDir(name) {
        const payload = {
            basedir: Api.basedir,
            name: name,
        };

        return $http().post('/cms/media', payload);
    },

    /**
     * Move files/folders to a new destination.
     *
     * @param objects
     * @param to
     * @return Promise
     */
    move(objects, to) {
        let payload = {
            files: objects.map(f => f.basename),
            target: to,
            basedir: Api.basedir,
        };

        return $http().post('/cms/media/move', payload);
    },

    /**
     * Remove specified file/folder or selected files/folders.
     *
     * @param object
     * @return Promise
     */
    remove(object) {
        let selected = object ? [object] : store.getters['selection/all'];

        return $http().post('/cms/media/remove', {
            files: selected.filter(file => file.isFile).map(file => file.basename),
            directories: selected.filter(file => file.isDir).map(file => file.basename),
        }).then(() => {
            selected.forEach((file) => {
                let index;

                index = store.getters['storage/position'](file);
                if (index > -1) {
                    store.dispatch('storage/removeFromPosition', index);
                }
            });
        });
    },

    rename(from, to) {
        return $http().post('/cms/media/rename', {from, to});
    },
};

export default Api;