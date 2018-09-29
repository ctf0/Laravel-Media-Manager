import omitBy from 'lodash/omitBy'
import isObject from 'lodash/isObject'
import Plyr from 'plyr'

export default {
    methods: {
        initPlyr() {
            let options = {
                debug: false,
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
                tooltips: {controls: false, seek: false},
                keyboard: {focused: false, global: false}
            }

            if (!this.smallScreen || (this.smallScreen && this.activeModal)) {
                let t = setInterval(() => {
                    let item = document.querySelector('[data-player]')

                    if (item) {
                        this.plyr = new Plyr(item, options)
                        clearInterval(t)
                    }
                }, 50)
            }
        },
        destroyPlyr() {
            if (this.plyr) this.plyr.destroy()
            this.plyr = null
        },
        playMedia() {
            return this.plyr ? this.plyr.togglePlay() : false
        },
        autoPlay() {
            if (this.filterNameIs('audio') || this.filterNameIs('video')) {
                let player = this.plyr

                player.on('ended', () => {
                    // stop at the end of list
                    if (this.currentFileIndex < this.allItemsCount - 1) {
                        // nav to next
                        this.goToNext()

                        // play navigated to
                        this.$nextTick(() => {
                            setTimeout(this.plyr.play(), 500)
                        })
                    }
                })
            }
        },

        // audio
        getAudioData(url) {
            return new Promise((resolve, reject) => {
                jsmediatags.read(url, {
                    onSuccess(tag) {
                        let val = tag.tags

                        if (val) {
                            if (val.picture) {
                                const {data, format} = val.picture
                                let base64String = ''

                                for (var value of data) {
                                    base64String += String.fromCharCode(value)
                                }

                                val.picture = `data:${format};base64,${window.btoa(base64String)}`
                            }

                            return resolve(omitBy(val, isObject))
                        }

                        return reject('no data found')
                    },
                    onError(error) {
                        return reject(error)
                    }
                })
            })
        }
    }
}
