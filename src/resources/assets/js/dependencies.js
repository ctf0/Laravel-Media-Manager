$.ajaxSetup({
    cache: false,
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
})

/*                Vue                */
window.Vue = require('vue')

/*                Libs                */
window.EventHub = require('vuemit')
window.keycode = require('keycode')
window.dropzone = require('dropzone')
Vue.use(require('vue-tippy'))
Vue.use(require('vue2-filters'))

/*                Components                */
import VueLightbox from 'vue-lightbox'
Vue.component('Lightbox', VueLightbox)

/*                BS Modal                */
require('./bootstrap_modal')
