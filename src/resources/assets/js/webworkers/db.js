const db = require('idb-keyval')
const store = new db.Store('ctf0-Media_Manager', 'media_manager')

onmessage = (e) => {
    let {type, key, val} = e.data

    switch (type) {
        case 'get':
            db.get(key, store)
                .then((res) => postMessage(res))
            break

        case 'set':
            db.set(key, val, store)
                .then(() => postMessage(true))
                .catch(() => postMessage(false))
            break

        case 'del':
            db.del(key, store)
                .then(() => postMessage(true))
                .catch(() => postMessage(false))
            break

        case 'clr':
            db.clear(store)
                .then(() => postMessage(true))
                .catch(() => postMessage(false))
            break

        case 'keys':
            db.keys(store)
                .then((res) => postMessage(res))
                .catch(() => postMessage(false))
            break
    }
}
