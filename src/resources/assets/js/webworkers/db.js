const db = require('idb-keyval')

self.addEventListener('message', async (e) => {
    let {type, key, val} = e.data

    switch (type) {
        case 'get':
            db.get(key)
                .then((res) => self.postMessage(res))
            break
        case 'set':
            db.set(key, val)
                .then(() => self.postMessage(true))
                .catch(() => self.postMessage(false))
            break
        case 'del':
            db.del(key)
                .then(() => self.postMessage(true))
                .catch(() => self.postMessage(false))
            break
        case 'clr':
            db.clear()
                .then(() => self.postMessage(true))
                .catch(() => self.postMessage(false))
            break
    }
})
