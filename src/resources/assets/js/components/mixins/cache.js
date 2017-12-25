export default {
    methods: {
        // ls
        getLs() {
            return this.$ls.get('ctf0-Media_Manager', {})
        },
        updateLs(obj) {
            let storage = this.getLs()

            Object.assign(storage, obj)
            this.$ls.set('ctf0-Media_Manager', storage)
        },

        // cache
        cacheResponse(value) {
            return localforage.setItem(this.cacheName, value).catch((err) => {
                console.warn('localforage.setItem', err)
            })
        },
        getCachedResponse() {
            return localforage.getItem(this.cacheName)
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

            let items = destination ? [extra, cacheName] : [cacheName]

            items.forEach((one) => {
                return localforage.removeItem(one).then(() => {
                    console.log(`${one} is cleared!`)
                }).catch((err) => {
                    console.warn('localforage.removeItem', err)
                })
            })
        }
    }
}
