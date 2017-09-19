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
import VueLightbox from 'vue-lightbox'
Vue.component('Lightbox', VueLightbox)

/*                Components                */
Vue.component('MediaManager', require('./components/' + process.env.MIX_MM_FRAMEWORK + '/media.vue'))
Vue.component('MyNotification', require('./components/' + process.env.MIX_MM_FRAMEWORK + '/notifs.vue'))

/*                Events                */
EventHub.listen('modal-show', () => {
    // ...
})

EventHub.listen('modal-hide', () => {
    // ...
})
