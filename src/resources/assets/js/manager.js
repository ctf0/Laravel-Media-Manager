/*                Libs                */
window.EventHub = require('vuemit')
window.keycode = require('keycode')
window.Fuse = require('fuse.js')

Vue.use(require('vue2-filters'))
Vue.use(require('vue-clipboard2'))
Vue.use(require('vue-ls'))
Vue.use(require('vue-async-computed'))
require('vue-list-rendered')

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
Vue.use(VueTouch)

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}
axios.interceptors.response.use(
    (response) => response,
    (error) => Promise.reject(error.response)
)

// Echo
// import EchoLib from 'laravel-echo'
// window.Echo = new EchoLib({
//     broadcaster: 'pusher',
//     key: 'your-pusher-key'
// });

// vue-awesome
require('./modules/icons')
Vue.component('icon', require('vue-awesome/components/Icon').default)

/*                Components                */
Vue.component('MediaManager', require('./components/manager.vue').default)
Vue.component('MyNotification', require('vue-notif').default)

/*                Events                */
if ('connection' in navigator) {
    if (!navigator.connection.saveData) {
        require('./modules/events')
    }
}
