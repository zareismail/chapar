Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'chapar',
      path: '/chapar',
      component: require('./components/Tool'),
    },
  ])
})
