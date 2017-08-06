/*                Vue                */
window.Vue = require('vue')

/*                Libs                */
window.EventHub = require('vuemit')
window.keycode = require('keycode')
window.dropzone = require('dropzone')
Vue.use(require('vue-tippy'))
Vue.use(require('vue2-filters'))

/*                Components                */
Vue.component('MyNotification', require('./components/' + process.env.MIX_MM_FRAMEWORK + '-notif.vue'))
import VueLightbox from 'vue-lightbox'
Vue.component('Lightbox', VueLightbox)

/*                BS Modal                */
require('./bootstrap_modal')

/*                Logic                */
require('./script')
