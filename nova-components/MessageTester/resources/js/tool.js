Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'message-tester',
            path: '/message-tester',
            component: require('./components/Tool'),
        },
    ])
})
