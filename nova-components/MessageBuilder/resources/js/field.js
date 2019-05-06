Nova.booting((Vue, router, store) => {
    Vue.component('index-message-builder', require('./components/IndexField'))
    Vue.component('detail-message-builder', require('./components/DetailField'))
    Vue.component('form-message-builder', require('./components/FormField'))
})
