const Selection = {
    namespaced: true,

    state: {
        selected: [],
    },

    mutations: {
        set(state, collection) {
            state.selected = collection;
        },

        clean(state) {
            state.selected = [];
        },
    },

    actions: {
        set(context, collection) {
            return context.commit('set', collection);
        },

        clean({commit}) {
            commit('clean');
        },
    },

    getters: {
        all(state) {
            return state.selected;
        },

        first(state) {
            return state.selected.length ? state.selected[0] : null;
        },

        exists(state) {
            return state.selected.length;
        },

        multiple(state) {
            return state.selected.length > 1;
        },

        count(state) {
            return state.selected.length;
        },

        info: (state) => (attribute) => {
            if (state.selected.length > 1) {
                return {};
            }

            return (attribute && state.selected[0].hasOwnProperty(attribute))
                ? state.selected[0][attribute]
                : state.selected;
        },

        has: (state) => (file) => {
            return state.selected.length && state.selected.filter(f => f.basename === file.basename).length;
        },
    },
};

export default Selection;