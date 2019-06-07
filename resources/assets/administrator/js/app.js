require('./bootstrap');

import store from './media/store/index';
import VueClip from 'vue-clip';
import Slider from 'vue-plain-slider';

Vue.use(VueClip);

new Vue({
    el: '#app',
    store,
    components: {
        Slider,
    },
});