import addMinutes from 'date-fns/add_minutes'
import getTime from 'date-fns/get_time'

import { Store, get, set, del, clear, keys } from 'idb-keyval'
const cacheStore = new Store(
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
            let storage = Object.assign(this.getLs(), obj)
            this.$ls.set('ctf0-Media_Manager', storage)
        },
        removeLs() {
            this.folders = []
            this.$ls.remove('ctf0-Media_Manager')
        },
        preSaved() {
            let ls = this.getLs()

            if (ls) {
                this.randomNames = ls.randomNames || false
                this.folders = ls.folders || []
                this.toolBar = ls.toolBar || true
                this.selectedFile = ls.selectedFileName || null
            }
        },

        // cache
        getCachedResponse(key = this.cacheName) {
            return get(key, cacheStore)
        },
        cacheResponse(val) {
            let date = getTime(addMinutes(new Date(), this.cacheExp))
            val = Object.assign(val, {expire: date})

            return set(this.cacheName, val, cacheStore).catch((err) => {
                console.warn('cacheStore.setItem', err)
            })
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
                return this.deleteCache(one)
            })
        },
        deleteCache(item) {
            return del(item, cacheStore).then(() => {
                console.log(`${item} ${this.trans('clear_cache')}`)
            }).catch((err) => {
                console.error('cacheStore.removeItem', err)
            })
        },
        clearCache(showNotif = true) {
            clear(cacheStore).then(() => {
                if (showNotif) {
                    this.showNotif(this.trans('clear_cache'))
                }

                setTimeout(() => {
                    this.refresh()
                }, 100)
            }).catch((err) => {
                console.error(err)
            })
        },
        invalidateCache() {
            let now = getTime(new Date())

            return keys(cacheStore).then((keys) => {
                keys.map((key) => {
                    this.getCachedResponse(key).then((item) => {
                        if (item.expire < now) {
                            return this.deleteCache(key)
                        }
                    })
                })
            }).catch((err) => {
                console.error(err)
            })
        }
    }
}
