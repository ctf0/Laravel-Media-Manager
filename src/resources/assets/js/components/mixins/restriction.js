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
            EventHub.listenOnce('get-folders', (data) => {
                if (data) {
                    return this.folders.push(...arr)
                }
            })
        },
        restrictAndLast() {
            return this.checkForRestrictedPath() && this.folders.length < 2
        },
        checkForRestrictedExt(file) {
            return this.restrictExt.includes(this.getExtension(file.name))
        }
    }
}
