import {clear, createStore, del, get, keys, set} from 'idb-keyval'

const store = createStore('ctf0-Media_Manager', 'media_manager')

onmessage = (e) => {
    let {type, key, val} = e.data

    switch (type) {
        case 'get':
            get(key, store)
                .then((res) => postMessage(res))
            break

        case 'set':
            set(key, val, store)
                .then(() => postMessage(true))
                .catch(() => postMessage(false))
            break

        case 'del':
            del(key, store)
                .then(() => postMessage(true))
                .catch(() => postMessage(false))
            break

        case 'clr':
            clear(store)
                .then(() => postMessage(true))
                .catch(() => postMessage(false))
            break

        case 'keys':
            keys(store)
                .then((res) => postMessage(res))
                .catch(() => postMessage(false))
            break
    }
}
