import router from './router/index';

import App from './views/App';

require('vue2-animate/dist/vue2-animate.min.css');

window.Vue = require('vue');

const { app } = new window.Vue({
  el: '#app',
  components: { App },
  router,
});
