import Vue from 'vue';
import Vuex from 'vuex';
import selection from './Selection';
import response from './Response';
import storage from './Storage';

Vue.use(Vuex);

const store = new Vuex.Store({
    debug: true,
    modules: {
        selection,
        response,
        storage,
    },
});

export default store;