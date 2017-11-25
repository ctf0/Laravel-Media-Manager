export default {
    methods: {
        checkForRestrictedPath() {
            return this.restrictPath !== ''
        },
        restrictAccess() {
            let path = this.restrictPath
            let arr = path.split('/')
            this.getFiles(arr)

            // show folders if we have something
            EventHub.listenOnce('get-folders', (check) => {
                if (check) {
                    return this.folders = arr.length > 1 ? [arr[arr.length - 1]] : arr
                }
            })
        },
        restrictAndLast() {
            return this.checkForRestrictedPath() && this.folders.length < 2
        },
        checkForRestrictedExt(file) {
            return this.hideExt.includes(this.getExtension(file.name))
        }
    }
}
