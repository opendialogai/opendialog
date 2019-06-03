require('./bootstrap');

import store from './media/store/index';
import VueClip from 'vue-clip';

Vue.use(VueClip);

new Vue({
    el: '#app',
    store,
});