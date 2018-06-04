export default {
    methods: {
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
                return this.hidePath.includes(
                    this.clearDblSlash(`${this.folders.join('/')}/${name}`)
                )
            }

            return this.hidePath.includes(name)
        },
        restrictModeIsOn() {
            return Boolean(this.restrict.path)
        },
        restrictUpload() {
            return Boolean(this.restrict.uploadTypes || this.restrict.uploadsize)
        },
        resolveRestrictFolders() {
            return this.folders = this.restrict.path.split('/')
        }
    }
}
