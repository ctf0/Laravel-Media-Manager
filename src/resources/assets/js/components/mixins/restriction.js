export default {
    methods: {
        checkForRestrictedPath() {
            return this.restrictPath
        },
        restrictAndLast() {
            return this.checkForRestrictedPath() && this.folders.length < 2
        },
        restrictAccess() {
            let path = this.restrictPath
            let arr = path.split('/')
            this.getFiles(arr)

            // show folders if we have something
            EventHub.listenOnce('get-folders', (check) => {
                if (check) {
                    return this.folders = arr.length > 0 ? [arr[arr.length - 1]] : arr
                }
            })
        },

        // hide
        checkForHiddenExt(file) {
            return this.hideExt.includes(this.getExtension(file.name))
        },
        checkForHiddenPath(folder) {
            if (this.fileTypeIs(folder, 'folder')) {

                return this.checkForFolderName(folder.name)
            }
        },
        checkForFolderName(name) {
            if (this.folders.length) {
                let path = this.folders.join('/')
                path = `${path}/${name}`.replace('//', '/')
                return this.hidePath.includes(path)
            }

            return this.hidePath.includes(name)
        }
    }
}
