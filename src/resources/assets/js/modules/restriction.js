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
            return Boolean(this.getResrtictedUploadTypes() || this.getResrtictedUploadSize())
        },
        resolveRestrictFolders() {
            return this.folders = this.getRestrictedPathArray()
        },
        getRestrictedPathArray() {
            return this.arrayFilter(this.resrtictPath.split('/'))
        },

        // getters
        getResrtictedPath() {
            return this.restrictions.path
        },
        getResrtictedUploadTypes() {
            return this.restrictions.uploadTypes
        },
        getResrtictedUploadSize() {
            return this.restrictions.uploadSize
        }
    },
    computed: {
        resrtictPath() {
            return this.getResrtictedPath()?.replace(/^\/+/, '') // remove starting /
        },
        restrictModeIsOn() {
            return Boolean(this.resrtictPath)
        },
        restrictPathIsCurrent() {
            return this.restrictModeIsOn && this.folders.join('/') == this.resrtictPath
        }
    }
}
