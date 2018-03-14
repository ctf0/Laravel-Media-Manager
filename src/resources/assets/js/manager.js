/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
window.keycode = require('keycode')
window.Dropzone = require('dropzone')

Vue.use(require('vue2-filters'))
Vue.use(require('vue-clipboard2'))
Vue.use(require('vue-ls'))

// vue-tippy
Vue.use(require('vue-tippy'), {
    arrow: true,
    touchHold: true,
    inertia: true,
    performance: true,
    flipDuration: 0,
    popperOptions: {
        modifiers: {
            preventOverflow: {enabled: false},
            hide: {enabled: false}
        }
    }
})

// v-touch
let VueTouch = require('vue-touch')
VueTouch.registerCustomEvent('dbltap', {type: 'tap', taps: 2})
VueTouch.registerCustomEvent('hold', {type: 'press', time: 500})
Vue.use(VueTouch, {name: 'v-touch'})

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}
axios.interceptors.response.use(
    (response) => {return response},
    (error) => {return Promise.reject(error.response)}
)

// vue-awesome
require('./modules/icons')
Vue.component('icon', require('vue-awesome/components/Icon'))

/*                Components                */
Vue.component('MediaManager', require('./components/media.vue'))
Vue.component('MyNotification', require('vue-notif'))

/*                Events                */
require('./modules/events')
