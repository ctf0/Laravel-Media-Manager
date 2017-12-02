/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
window.keycode = require('keycode')
window.Dropzone = require('dropzone')
Vue.use(require('vue-tippy'))
Vue.use(require('vue2-filters'))
Vue.use(require('vue-clipboard2'))
Vue.use(require('vue-ls'))

// v-touch
let VueTouch = require('vue-touch')
VueTouch.registerCustomEvent('dbltap', {
    type: 'tap',
    taps: 2
})
Vue.use(VueTouch, {name: 'v-touch'})

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}

/*                Components                */
Vue.component('MediaManager', require('./components/media.vue'))
Vue.component('MyNotification', require('vue-notif'))

/*                Events                */
require('./events')
