import addMinutes from 'date-fns/add_minutes'
import getTime from 'date-fns/get_time'

import { Store, get, set, del, clear, keys } from 'idb-keyval'
const cacheStore = new Store(
    'ctf0-Media_Manager',   // db
    'laravel-media-manager' // store
)

export default {
    methods: {
        /*                Local Storage                */
        getLs() {
            return this.$ls.get(this.CDBN, {})
        },
        updateLs(obj) {
            let storage = Object.assign(this.getLs(), obj)
            this.$ls.set(this.CDBN, storage)
        },
        clearLs() {
            this.folders = []
            this.$ls.remove(this.CDBN)
        },
        preSaved() {
            let ls = this.getLs()

            if (ls) {
                this.randomNames = ls.randomNames || false
                this.folders = ls.folders || []
                this.lockedList = ls.lockedList || []
                this.toolBar = ls.toolBar || true
                this.selectedFile = ls.selectedFileName || null
            }
        },

        /*                IndexedDb                */
        getCachedResponse(key = this.cacheName) {
            return get(key, cacheStore)
        },
        cacheResponse(val, key = this.cacheName) {
            let date = getTime(addMinutes(new Date(), this.config.cacheExp))
            val = Object.assign(val, {expire: date})

            return set(key, val, cacheStore).catch((err) => {
                console.warn('cacheStore.setItem', err)
            })
        },
        removeCachedResponse(destination = null, extra = []) {
            let cacheName = this.getCacheName(destination || this.cacheName)
            let items = ['root_']
            let promises = []

            items.push(...this.getRecursivePathParent(cacheName))

            if (extra.length) {
                extra.forEach((e) => {
                    items.push(...this.getRecursivePathParent(e))
                })
            }

            // clear dups and delete items
            Array.from(new Set(items)).forEach((one) => {
                promises.push(this.deleteCachedResponse(one))
            })

            return Promise.all(promises)
        },
        deleteCachedResponse(item) {
            return del(item, cacheStore).then(() => {
                console.log(`${item} ${this.trans('clear_cache')}`)
            }).catch((err) => {
                console.error('cacheStore.removeItem', err)
            })
        },
        clearCache(notif = true) {
            clear(cacheStore).then(() => {
                this.refresh().then(() => {
                    if (notif) this.showNotif(this.trans('clear_cache'))
                })

            }).catch((err) => {
                console.error(err)
            })
        },
        invalidateCache() {
            let now = getTime(new Date())

            return keys(cacheStore).then((keys) => {
                return Promise.all(
                    keys.map((key) => {
                        return this.getCachedResponse(key).then((item) => {
                            if (item.expire <= now) {
                                return this.deleteCachedResponse(key)
                            }
                        })
                    })
                )
            }).catch((err) => {
                console.error(err)
            })
        },

        // helpers
        getCacheName(path) {
            let str = this.cacheName == 'root_'
                ? path == 'root_'
                    ? path
                    : `/${path}`
                : this.cacheName == path
                    ? `/${path}`
                    : `${this.cacheName}/${path}`

            return this.clearDblSlash(str)
        },
        getRecursivePathParent(path) {
            let list = []
            let arr = path.split('/').filter((e) => e)
            let i = arr.length - 1

            for (i; i >= 0; i--) {
                list.push(path)            // add current
                arr.pop()                  // remove last
                path = `/${arr.join('/')}` // regroup remaining
            }

            return list
        },

        /*                Cache Storage Api                */
        removeImageCache(url) {
            if ('caches' in window) {
                return caches.open(this.CDBN).then((cache) => {
                    return cache.delete(url)
                })
            }
        },
        clearImageCache() {
            if ('caches' in window) {
                caches.keys().then((keys) => {
                    return Promise.all(
                        keys.map((item) => {
                            if (item == this.CDBN) {
                                return caches.delete(item)
                            }
                        })
                    )
                })
            }
        }
    }
}