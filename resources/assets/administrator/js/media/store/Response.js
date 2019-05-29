function emptyState() {
    return {
        data: {},
        status: null,
        statusText: ''
    };
}

const Response = {
    namespaced: true,

    state: emptyState(),

    mutations: {
        set(state, value) {
            state = Object.assign(state, value);
        },
    },

    actions: {
        set({commit}, value) {
            commit('set', value);

            setTimeout(() => {
                commit('set', emptyState());
            }, 3000);
        },
    },

    getters: {
        success(state) {
            return String(state.status).match(/^20\d+/g);
        },

        message(state) {
            return state.hasOwnProperty('data') && state.data.hasOwnProperty('message')
                ? state.data.message
                : state.statusText;
        },
    },
};

export default Response;
