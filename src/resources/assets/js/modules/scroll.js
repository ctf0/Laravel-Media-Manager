import cloneDeep from 'lodash/cloneDeep'
import animateScrollTo from '../packages/animated-scroll-to'

export default {
    methods: {
        scrollOnLoad() {
            // scroll & click on prev selected item
            if (!this.no_files && this.currentFileIndex) {
                this.scrollToFile(this.getElementByIndex(this.currentFileIndex))
            }

            // scroll to breadcrumb item
            if (this.$refs.bc) {
                let folders = cloneDeep(this.folders).pop()
                this.scrollMobileBc(folders, this.$refs.bc)
            }
        },
        scrollToFile(file) {
            if (file) {
                file.click()

                // make sure scrolling fires
                setTimeout(this.scrollToSelected(file), 500)
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
        updateScrollByRow() {
            const fileslist = this.$refs.filesList
            const cont = fileslist || null

            if (cont) {
                let width = cont.clientWidth
                let pad = parseInt(window.getComputedStyle(cont).paddingLeft) + parseInt(window.getComputedStyle(cont).paddingRight)
                let contWidth = width - pad
                let itemWidth = fileslist.firstChild ? fileslist.firstChild.clientWidth : 0

                this.scrollByRowItemsCount = Math.floor(contWidth / itemWidth)
            }
        }
    }
}
