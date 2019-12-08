require('../packages/PreventGhostClick')

export default {
    methods: {
        // files wrapper gestures
        containerClick(e, cls = '__stack-files') {
            let type = e.type

            if (e.target.classList.contains(cls)) {
                if (type == 'hold') {
                    !this.isBulkSelecting()
                        ? document.querySelector('.dz-clickable').click()
                        : this.toggleOverlay()
                }

                if (type == 'dbltap') {
                    this.createNewFolder()
                }

                if (type == 'pinchin') {
                    this.refresh()
                }
            }
        },

        cardSwipGesture(e) {
            EventHub.fire('stopHammerPropagate')

            switch (e.type) {
                case 'swipeup':
                    this.goToNextRow()
                    break
                case 'swipedown':
                    this.goToPrevRow()
                    break
                case 'swiperight':
                    this.goToPrev()
                    break
                case 'swipeleft':
                    this.goToNext()
                    break
            }

            this.checkTypeB4Navigation()
        },
        swipGesture(e, file, i) {
            EventHub.fire('stopHammerPropagate')
            this.setSelected(file, i)

            let target = e.target
            let style = {}
            let cls

            if (!target.classList.contains('__file-box')) {
                target = target.closest('.__file-box')
            }

            switch (e.type) {
                case 'swipeup':
                    style = {transform: `translateY(${e.deltaY}px)`}
                    cls = 'bounceInDown'
                    this.addToMovableList(file)
                    break
                case 'swipedown':
                    style = {transform: `translateY(${e.deltaY}px)`}
                    cls = 'bounceInUp'
                    this.deleteItem()
                    break
                case 'swipeleft':
                    style = {transform: `translateX(${e.deltaX}px)`}
                    cls = 'bounceInRight'
                    this.renameItem()
                    break
                case 'swiperight':
                    style = {transform: `translateX(${e.deltaX}px)`}
                    cls = 'bounceInLeft'
                    this.renameItem()
                    break
            }

            // apply animation
            Object.assign(target.style, style, {
                'z-index': 1
            })

            if (cls) {
                setTimeout(() => { // transition
                    target.classList.add(cls)
                    Object.assign(target.style, {
                        'transform': ''
                    })

                    setTimeout(() => { // animation
                        target.classList.remove(cls)
                        Object.assign(target.style, {
                            'transform': '',
                            'z-index': ''
                        })
                    }, 750)
                }, 250)
            }
        },
        pressGesture(e, file, i) {
            let type = e.type
            let target = e.target
            let style = {}
            let cls

            if (!target.classList.contains('__file-box')) {
                target = target.closest('.__file-box')
            }

            switch (type) {
                case 'hold':
                    style = {transform: 'scale(0.8)'}
                    cls = 'jackInTheBox'
                    break
                case 'dbltap':
                    style = {transform: 'scale(0.8)'}
                    cls = 'bounceIn'
                    break
            }

            // apply animation
            Object.assign(target.style, style, {
                'z-index': 1
            })

            if (cls) {
                setTimeout(() => { // transition
                    target.classList.add(cls)
                    Object.assign(target.style, {
                        'transform': ''
                    })

                    setTimeout(() => { // animation
                        target.classList.remove(cls)
                        Object.assign(target.style, {
                            'transform': '',
                            'z-index': ''
                        })
                    }, 750)
                }, 250)
            }

            // operations
            if (type == 'hold') {
                this.setSelected(file, i)
                this.imageEditor()

                // anything but images, toggle lock
                if (!this.selectedFileIs('image')) {
                    this.lockFileForm()
                }
            }

            if (type == 'dbltap') {
                this.dbltap(e)
            }
        },
        dbltap(e) {
            PreventGhostClick(e.target)

            if (!this.isBulkSelecting()) {
                // image / text
                if (this.selectedFileIs('image') || this.selectedFileIs('pdf') || this.textFileType()) {
                    return this.toggleModal('preview_modal')
                }
                // media
                else if (this.selectedFileIs('video') || this.selectedFileIs('audio')) {
                    return !this.infoSidebar || this.isASmallScreen
                        ? this.toggleModal('preview_modal')
                        : this.playMedia()
                }
                // folder
                else if (this.selectedFileIs('folder')) {
                    this.openFolder(this.selectedFile)
                }
                // other
                else {
                    this.saveFile(this.selectedFile)
                }
            }
        }
    }
}
