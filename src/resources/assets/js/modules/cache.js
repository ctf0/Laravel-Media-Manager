import addMinutes from 'date-fns/add_minutes'
import getTime from 'date-fns/get_time'

import { Store, get, set, del, clear, keys } from 'idb-keyval'
const DBNAME = 'ctf0-Media_Manager'
const cacheStore = new Store(
    DBNAME, // db
    'laravel-media-manager' // store
)

export default {
    methods: {
        // local storage
        getLs() {
            return this.$ls.get(DBNAME, {})
        },
        updateLs(obj) {
            let storage = Object.assign(this.getLs(), obj)
            this.$ls.set(DBNAME, storage)
        },
        removeLs() {
            this.folders = []
            this.$ls.remove(DBNAME)
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

        // cache
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
            let cacheName = this.cacheName
            let items = ['root_']
            let promises = []

            // current path only
            if (!destination) {
                return this.deleteCache(cacheName)
            }

            // up & down
            destination == '../' ? false : cacheName = this.getCacheName(destination)

            // clear nested folders cache too
            items.push(...this.getRecursivePathParent(cacheName))
            if (extra.length) {
                extra.forEach((e) => {
                    items.push(...this.getRecursivePathParent(e))
                })
            }

            // clear dups and delete items
            Array.from(new Set(items)).forEach((one) => {
                promises.push(this.deleteCache(one))
            })

            return Promise.all(promises)
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
                return Promise.all(
                    keys.map((key) => {
                        return this.getCachedResponse(key).then((item) => {
                            if (item.expire <= now) {
                                return this.deleteCache(key)
                            }
                        })
                    })
                )
            }).catch((err) => {
                console.error(err)
            })
        },

        /*                helpers                */
        getCacheName(path) {
            let str = this.cacheName == 'root_'
                ? `/${path}`
                : `${this.cacheName}/${path}`

            return str.replace('//', '/')
        },
        getRecursivePathParent(path) {
            let list = []
            let arr = path.split('/').filter((e) => e)
            let i = arr.length - 1

            for (i; i >= 0; i--) {
                list.push(path)           // add current
                arr.pop()                       // remove last
                path = `/${arr.join('/')}` // regroup remaining
            }

            return list
        }
    }
}