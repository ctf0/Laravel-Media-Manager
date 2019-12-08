const db = require('idb-keyval')
const store = new db.Store('ctf0-Media_Manager', 'media_manager')

self.addEventListener('message', async (e) => {
    let {type, key, val} = e.data

    switch (type) {
        case 'get':
            db.get(key, store)
                .then((res) => self.postMessage(res))
            break
        case 'set':
            db.set(key, val, store)
                .then(() => self.postMessage(true))
                .catch(() => self.postMessage(false))
            break
        case 'del':
            db.del(key, store)
                .then(() => self.postMessage(true))
                .catch(() => self.postMessage(false))
            break
        case 'clr':
            db.clear(store)
                .then(() => self.postMessage(true))
                .catch(() => self.postMessage(false))
            break
        case 'keys':
            db.keys(store)
                .then((res) => self.postMessage(res))
                .catch(() => self.postMessage(false))
            break
    }
})
