export default {
    methods: {
        checkForUrlQuery() {
            return window.location.href.split('?')[0]
        },
        clearUrlQuery() {
            window.history.replaceState(null, null, window.location.href.replace(/\?path.*/, ''))
        },
        getPathFromUrl() {
            return new Promise((resolve) => {
                if (!this.inModal) {
                    let path = window.location.search
                    this.folders = path.includes('path') ? path.replace('?path=', '').split('/') : []
                }

                return resolve()
            })
        },
        updatePageUrl() {
            if (!this.inModal) {
                let url = this.checkForUrlQuery()

                history.pushState(null, null,
                    this.folders.length
                        ? `${url}?path=${this.folders.join('/')}`
                        : url
                )
            }
        },
        urlNavigation(e) {
            if (!this.inModal) {
                this.getPathFromUrl().then(() => {
                    this.getFiles(this.folders)
                })
            }
        }
    }
}
