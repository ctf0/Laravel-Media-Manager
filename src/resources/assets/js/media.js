/*                Libs                */
window.keycode = require('keycode')
window.dropzone = require('dropzone')
Vue.use(require('vue-tippy'))
Vue.use(require('vue2-filters'))

/*                Components                */
Vue.component('MediaManager', require('./components/' + process.env.MIX_MM_FRAMEWORK + '/media.vue'))

/*                Events                */
require('./events')
