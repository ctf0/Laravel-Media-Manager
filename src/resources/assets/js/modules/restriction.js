export default {
    methods: {
        // hide
        checkForHiddenExt(file) {
            return this.hideExt.includes(this.getExtension(file.name))
        },
        checkForHiddenPath(folder) {
            return this.fileTypeIs(folder, 'folder') && this.checkForFolderName(folder.storage_path)
        },
        checkForFolderName(path) {
            return this.hidePath.some((e) => e == path)
        },

        // restrict
        restrictUpload() {
            return Boolean(this.restrict.uploadTypes || this.restrict.uploadsize)
        },
        resolveRestrictFolders() {
            return this.folders = this.getRestrictedPathArray()
        },
        getRestrictedPathArray() {
            return this.arrayFilter(this.resrtictPath.split('/'))
        }
    },
    computed: {
        resrtictPath() {
            let path = this.restrict.path

            return path ? path.replace(/^\/+/, '') : ''
        },
        restrictModeIsOn() {
            return Boolean(this.resrtictPath)
        },
        restrictPathIsCurrent() {
            return this.restrictModeIsOn && this.folders.join('/') == this.resrtictPath
        }
    }
}
