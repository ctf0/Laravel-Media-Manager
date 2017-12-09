/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
window.keycode = require('keycode')
window.Dropzone = require('dropzone')
Vue.use(require('vue2-filters'))
Vue.use(require('vue-clipboard2'))
Vue.use(require('vue-ls'))
Vue.use(require('vue-tippy'), {
    flipDuration: 0,
    arrow: true,
    touchHold: true,
    performance: true,
    popperOptions: {
        modifiers: {
            preventOverflow: {
                enabled: false
            }
        }
    }
})

// v-touch
let VueTouch = require('vue-touch')
VueTouch.registerCustomEvent('dbltap', {
    type: 'tap',
    taps: 2
})
Vue.use(VueTouch, {name: 'v-touch'})

// directive
require('vue-multi-ref')

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}

// polyfill
// window.__forceSmoothScrollPolyfill__ = true
require('smoothscroll-polyfill').polyfill()

// vue-awesome
import 'vue-awesome/icons/cloud-upload'
import 'vue-awesome/icons/folder'
import 'vue-awesome/icons/refresh'
import 'vue-awesome/icons/share'
import 'vue-awesome/icons/i-cursor'
import 'vue-awesome/icons/trash'
import 'vue-awesome/icons/minus'
import 'vue-awesome/icons/plus'
import 'vue-awesome/icons/puzzle-piece'
import 'vue-awesome/icons/image'
import 'vue-awesome/icons/video-camera'
import 'vue-awesome/icons/music'
import 'vue-awesome/icons/file-text'
import 'vue-awesome/icons/times'
import 'vue-awesome/icons/bell-o'
import 'vue-awesome/icons/search'
import 'vue-awesome/icons/angle-double-right'
import 'vue-awesome/icons/angle-double-left'
import 'vue-awesome/icons/bars'
import 'vue-awesome/icons/clone'
import 'vue-awesome/icons/file-pdf-o'
import 'vue-awesome/icons/mouse-pointer'
import 'vue-awesome/icons/file-text-o'
import 'vue-awesome/icons/download'
import 'vue-awesome/icons/warning'
import 'vue-awesome/icons/archive'
import 'vue-awesome/icons/unlock'
import 'vue-awesome/icons/lock'
Vue.component('icon', require('vue-awesome/components/Icon'))

/*                Components                */
Vue.component('MediaManager', require('./components/media.vue'))
Vue.component('MyNotification', require('vue-notif'))

/*                Events                */
require('./events')
