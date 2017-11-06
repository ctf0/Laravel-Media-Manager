$.ajaxSetup({
    cache: false,
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
})

/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
window.keycode = require('keycode')
window.dropzone = require('dropzone')
Vue.use(require('vue-tippy'))
Vue.use(require('vue2-filters'))

let VueTouch = require('vue-touch')
VueTouch.registerCustomEvent('dbltap', {
    type: 'tap',
    taps: 2
})
Vue.use(VueTouch, {name: 'v-touch'})

/*                Components                */
Vue.component('MediaManager', require('./components/media-' + process.env.MIX_MM_FRAMEWORK + '.vue'))
Vue.component('MyNotification', require('vue-notif'))

/*                Events                */
require('./events')
