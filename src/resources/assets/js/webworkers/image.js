// https://gist.github.com/ahem/d19ee198565e20c6f5e1bcd8f87b3408

function createWorker(f) {
    return new Worker(URL.createObjectURL(new Blob([`(${f})()`])))
}

const imgWorker = createWorker(() => {
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

export function loadImageWithWorker(src) {
    return new Promise((resolve, reject) => {
        function handler(e) {
            let data = e.data

            if (data.src === src) {
                imgWorker.removeEventListener('message', handler)

                data.error
                    ? reject(data.error)
                    : resolve(data.bitmap)
            }
        }

        imgWorker.addEventListener('message', handler)
        imgWorker.postMessage(src)
    })
}
