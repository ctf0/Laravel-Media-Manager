import { Store, set, get, del, clear } from 'idb-keyval'
const idbKeyVal = new Store(
    'ctf0-Media_Manager', // db
    'laravel-media-manager' // store
)

export default {
    methods: {
        // local storage
        getLs() {
            return this.$ls.get('ctf0-Media_Manager', {})
        },
        updateLs(obj) {
            let storage = this.getLs()

            Object.assign(storage, obj)
            this.$ls.set('ctf0-Media_Manager', storage)
        },
        removeLs() {
            this.folders = []
            this.$ls.remove('ctf0-Media_Manager')
            // location.reload()
        },

        // cache
        cacheResponse(value) {
            return set(this.cacheName, value, idbKeyVal).catch((err) => {
                console.warn('cacheStore.setItem', err)
            })
        },
        getCachedResponse() {
            return get(this.cacheName, idbKeyVal)
        },
        removeCachedResponse(destination = null) {
            let cacheName = this.cacheName
            let extra

            if (destination) {
                extra = destination == '../'
                    // go up
                    ? cacheName.split('/').length > 2 ? cacheName.replace(/\/[^/]+$/, '') : 'root_'
                    // go down
                    : cacheName == 'root_' ? `/${destination}` : `${cacheName}${destination}`
            }

            // avoid clearing twice
            let items = destination
                ? extra == cacheName ? [cacheName] : [extra, cacheName]
                : [cacheName]

            items.forEach((one) => {
                return del(one, idbKeyVal).then(() => {
                    console.log(`${one} ${this.trans('clear_cache')}`)
                }).catch((err) => {
                    console.warn('cacheStore.removeItem', err)
                })
            })
        },
        clearCache(showNotif = true) {
            clear(idbKeyVal).then(() => {
                if (showNotif) {
                    this.showNotif(this.trans('clear_cache'))
                }

                setTimeout(() => {
                    this.refresh()
                }, 100)
            }).catch((err) => {
                console.error(err)
            })
        }
    }
}
