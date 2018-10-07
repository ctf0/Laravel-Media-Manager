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
                        this.player.item = new Plyr(item, options)

                        let plr = this.player.item

                        // status
                        plr.on('playing', (e) => {
                            this.player.playing = true
                        })
                        plr.on('pause', (e) => {
                            this.player.playing = false
                        })

                        // fs
                        plr.on('enterfullscreen', (e) => {
                            this.player.fs = true
                            document.querySelector('[data-plyr="fullscreen"]').blur()
                        })
                        plr.on('exitfullscreen', (e) => {
                            this.player.fs = false
                        })

                        clearInterval(t)
                    }
                }, 50)
            }
        },
        destroyPlyr() {
            if (this.player.item) this.player.item.destroy()
            this.player = {
                item: null,
                fs: false,
                playing: false
            }
        },
        playMedia() {
            if (this.player.item) {
                this.player.item.togglePlay()
            }
        },
        autoPlay() {
            if (this.filterNameIs('audio') || this.filterNameIs('video')) {
                this.player.item.on('ended', () => {
                    // stop at the end of list
                    if (this.currentFileIndex < this.allItemsCount - 1) {
                        // nav to next
                        this.goToNext()

                        // play navigated to
                        this.$nextTick(() => {
                            setTimeout(this.player.item.play(), 500)
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
