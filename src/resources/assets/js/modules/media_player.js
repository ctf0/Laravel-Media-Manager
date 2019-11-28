import Plyr from 'plyr'
import AudioWorker from 'worker-loader!../webworkers/audio'
import omit from 'lodash/omit'

export default {
    methods: {
        initPlyr() {
            let options = {
                debug: false,
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
                tooltips: {controls: true, seek: true},
                keyboard: {focused: false, global: false}
            }

            if (!this.smallScreen || (this.smallScreen && this.activeModal)) {
                let t = setInterval(() => {
                    let item = document.querySelector('[data-player]')

                    if (item) {
                        let plr = this.player.item = new Plyr(item, options)

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
            if (
                (this.filterNameIs('audio') || this.filterNameIs('video')) &&
                this.player.playing
            ) {
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
            return this.db('get', url).then((cache) => {
                // cached
                if (cache) {
                    this.audioFileMeta = cache
                }

                // fetch
                // we do that even when cache is found because
                // cover could be corrupted when loaded from cache
                // so we have to refetch it
                // also to save space we cache all except 'cover'
                const audio = new AudioWorker()
                audio.addEventListener('message', ({data}) => {
                    if (!cache) {
                        this.db('set', url, omit(data, ['cover']))
                    }

                    this.audioFileMeta = data
                })
                audio.postMessage(url)
            })
        },
        checkAudioData() {
            let selected = this.audioFileMeta

            if (selected) {
                return [
                    'artist',
                    'title',
                    'album',
                    'track',
                    'year'
                ].some((prop) => {
                    return selected.hasOwnProperty(prop)
                })
            }

            return false
        }
    }
}
