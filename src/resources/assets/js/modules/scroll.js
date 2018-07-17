import animateScrollTo from '../packages/animated-scroll-to'

export default {
    methods: {
        scrollOnLoad(folders) {
            // scroll & click on prev selected item
            if (this.currentFileIndex) {
                this.scrollToFile(this.getElementByIndex(this.currentFileIndex))
            }

            // scroll to breadcrumb item
            if (this.$refs.bc) {
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
                maxDuration: 500,
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
            const cont = this.$refs['__stack-files'].$el

            let width = cont.clientWidth
            let pad = parseInt(window.getComputedStyle(cont).paddingLeft) + parseInt(window.getComputedStyle(cont).paddingRight)
            let contWidth = width - pad
            let itemWidth = this.$refs.filesList.firstChild ? this.$refs.filesList.firstChild.clientWidth : 0

            this.scrollByRows = Math.floor(contWidth / itemWidth)
        }
    }
}
