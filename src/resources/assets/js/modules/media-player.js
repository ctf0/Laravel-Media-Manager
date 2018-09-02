import debounce from 'lodash/debounce'
import Plyr from 'plyr'

export default {
    methods: {
        initPlyr() {
            let options = {
                debug: false,
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen']
            }

            this.plyr = new Plyr(document.querySelector('[data-player]'), options)
        },
        destroyPlyr() {
            if (this.plyr) this.plyr.destroy()
            this.plyr = null
        },
        playMedia() {
            return this.plyr.togglePlay()
        },
        autoPlay() {
            if (this.filterNameIs('audio') || this.filterNameIs('video')) {
                let player = this.plyr

                player.on('ended', (e) => {
                    // stop at the end of list
                    if (this.currentFileIndex < this.allItemsCount - 1) {
                        // nav to next
                        this.goToNext()

                        // play navigated to
                        this.$nextTick(debounce(() => {
                            this.plyr.play()
                        }, 500))
                    }
                })
            }
        }
    }
}
