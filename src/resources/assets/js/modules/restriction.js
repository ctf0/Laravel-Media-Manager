export default {
    methods: {
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
                path = this.clearDblSlash(`${path}/${name}`)
                return this.hidePath.includes(path)
            }

            return this.hidePath.includes(name)
        }
    }
}