Nova.booting((Vue, router) => {
    Vue.component('index-message-type', require('./components/IndexField'));
    Vue.component('detail-message-type', require('./components/DetailField'));
    Vue.component('form-message-type', require('./components/FormField'));
})
