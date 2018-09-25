import animateScrollTo from '../packages/animated-scroll-to'

export default {
    methods: {
        /* https://gomakethings.com/detecting-when-a-visitor-has-stopped-scrolling-with-vanilla-javascript/ */
        scrollObserve() {
            let isScrolling

            this.$refs['__stack-files'].$el.addEventListener('scroll', () => {
                EventHub.fire('stop-img-observing')
                window.clearTimeout(isScrolling)

                isScrolling = setTimeout(() => {
                    return EventHub.fire('start-img-observing')
                }, 100)
            }, false)
        },

        // ops
        scrollOnLoad() {
            // scroll & click on prev selected item
            if (!this.no_files && this.currentFileIndex) {
                this.scrollToFile(this.getElementByIndex(this.currentFileIndex))
            }

            // scroll to breadcrumb item
            if (this.$refs.bc) {
                let folders = this.clearDblSlash(`/${this.folders.join('/')}`)
                this.scrollMobileBc(folders.split('/').pop(), this.$refs.bc)
            }
        },
        scrollToFile(file) {
            if (file) {
                file.click()
                this.scrollToSelected(file)
            }
        },
        scrollToSelected(file) {
            return animateScrollTo(file, {
                speed: 150,
                maxDuration: 2000,
                offset: -20,
                element: this.$refs['__stack-files'].$el,
                useKeys: true
            })
        },
        scrollMobileBc(name, bc) {
            animateScrollTo(document.getElementById(`${name ? name : 'library'}-bc`), {
                speed: 150,
                maxDuration: 500,
                horizontal: true,
                element: bc.$el
            })
        },
        scrollByRow() {
            const cont = this.$refs['__stack-files'] ? this.$refs['__stack-files'].$el : null

            if (cont) {
                let width = cont.clientWidth
                let pad = parseInt(window.getComputedStyle(cont).paddingLeft) + parseInt(window.getComputedStyle(cont).paddingRight)
                let contWidth = width - pad
                let itemWidth = this.$refs.filesList.firstChild ? this.$refs.filesList.firstChild.clientWidth : 0

                this.scrollByRows = Math.floor(contWidth / itemWidth)
            }
        }
    }
}
