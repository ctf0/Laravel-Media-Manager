function createWorker(f) {
    return new Worker(URL.createObjectURL(new Blob([`(${f})()`])))
}

const worker = createWorker(() => {
    self.addEventListener('message', (e) => {
        const src = e.data

        fetch(src)
            .then((response) => response.blob())
            .then((blob) => {
                let bitmap = URL.createObjectURL(blob)
                self.postMessage({src, bitmap})
            })
    })
})

export default function loadImageWithWorker(src) {
    return new Promise((resolve, reject) => {
        function handler(e) {
            if (e.data.src === src) {
                worker.removeEventListener('message', handler)
                if (e.data.error) {
                    reject(e.data.error)
                }
                resolve(e.data.bitmap)
            }
        }

        worker.addEventListener('message', handler)
        worker.postMessage(src)
    })
}
