const Storage = {
    namespaced: true,

    state: {
        files: window.mediaFiles || [],
    },

    mutations: {
        push(state, value) {
            state.files.push(value);
        },

        remove(state, object) {
            state.files.splice(state.files.indexOf(object), 1);
        },

        removeFromPosition(state, position) {
            state.files.splice(position, 1);
        },

        rename(state, payload) {
            state.files = state.files.map(file => {
                if (file.basename === payload.from) {
                    return payload.to;
                }
                return file;
            });
        },
    },

    actions: {
        push({commit}, value) {
            commit('push', value);
        },

        remove({commit}, object) {
            if (Array.isArray(object)) {
                object.forEach(file => {
                    commit('remove', file);
                });

                return;
            }

            commit('remove', object);
        },

        rename({commit}, payload) {
            commit('rename', payload);
        },

        removeFromPosition({commit}, position) {
            commit('removeFromPosition', position);
        },
    },

    getters: {
        files(state) {
            return (state.files || []).filter(file => !file.isDir);
        },

        directories(state) {
            let directories = (state.files || []).filter(file => file.isDir);

            if (window.REQUEST_PATH.length) {
                directories = ['../'].concat(directories);
            }

            return directories;
        },

        position: (state) => (file) => {
            return state.files.indexOf(file);
        },
    },
};

export default Storage;