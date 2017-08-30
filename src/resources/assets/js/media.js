require('./dependencies')

Vue.component('MediaManager', require('./components/' + process.env.MIX_MM_FRAMEWORK + '/media.vue'))
Vue.component('MyNotification', require('./components/' + process.env.MIX_MM_FRAMEWORK + '/notifs.vue'))
