export default {
    methods: {
        getUrlWithoutQuery() {
            let params = new URLSearchParams(location.search)

            return params.has('path')
                ? location.href.replace(new RegExp(`[?&]path=${params.get('path')}`), '')
                : location.href
        },
        clearUrlQuery() {
            history.replaceState(null, null, this.getUrlWithoutQuery())
        },
        getPathFromUrl() {
            return new Promise((resolve) => {
                if (!this.inModal) {
                    let params = new URLSearchParams(location.search)

                    this.folders = params.has('path')
                        ? this.arrayFilter(params.get('path').replace(/#/g, '').split('/'))
                        : []
                }

                return resolve()
            })
        },
        updatePageUrl() {
            if (!this.inModal && !this.restrictModeIsOn) {
                let full_url = this.getUrlWithoutQuery()
                let current_qs = new URL(full_url).search
                let params = new URLSearchParams(current_qs)
                let base = full_url.replace(current_qs, '')
                let folders = this.folders

                if (folders.length) {
                    params.append('path', folders.join('/'))
                }

                history.pushState(
                    null,
                    null,
                    current_qs
                        ? `${base}?${params.toString()}`
                        : full_url
                )
            }
        },
        urlNavigation(e) {
            if (!this.inModal) {
                this.getPathFromUrl().then(this.getFiles())
            }
        }
    }
}
