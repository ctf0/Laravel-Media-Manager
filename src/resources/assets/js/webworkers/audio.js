const omitBy = require('lodash/omitBy')
const isObject = require('lodash/isObject')

function createWorker(f) {
    return new Worker(URL.createObjectURL(new Blob([`(${f})()`])))
}

const audioWorker = createWorker(() => {
    self.addEventListener('message', (e) => {
        const location = e.data

        jsmediatags.read(location, {
            onSuccess(tags) {
                self.postMessage({location: location, type: 'success', data: tags})
            },
            onError(error) {
                self.postMessage({location: location, type: 'error', error: error})
            }
        })
    })
})

export function loadAudioImageWithWorker(url) {
    return new Promise((resolve, reject) => {
        function handler(e) {
            let data = e.data

            console.log(data)

            if (data.type === 'success') {
                audioWorker.removeEventListener('message', handler)

                let val = data.tags

                if (val) {
                    if (val.picture) {
                        const {data, format} = val.picture
                        let base64String = ''

                        for (let value of data) {
                            base64String += String.fromCharCode(value)
                        }

                        val.picture = `data:${format};base64, ${window.btoa(base64String)}`
                    }

                    resolve(omitBy(val, isObject))
                }

                reject('no data found')
            } else {
                reject(data.error)
            }
        }

        audioWorker.addEventListener('message', handler)
        audioWorker.postMessage(url)
    })
}
