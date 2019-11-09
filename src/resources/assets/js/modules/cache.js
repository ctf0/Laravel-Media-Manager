export default {
    methods: {
        /*                Local Storage                */
        getLs() {
            return this.$ls.get(this.CDBN, {})
        },
        updateLs(obj) {
            let storage = Object.assign(this.getLs(), obj)
            this.$ls.set(this.CDBN, storage)
        },
        clearLs() {
            if (!this.restrictModeIsOn()) {
                this.folders = []
            }

            this.$ls.remove(this.CDBN)
        },
        preSaved() {
            let ls = this.getLs()

            if (Object.keys(ls).length) {
                this.randomNames = ls.randomNames || false
                this.folders = ls.folders || []
                this.lockedList = ls.lockedList || []
                this.toolBar = ls.toolBar || true
                this.selectedFile = ls.selectedFileName || null
            }
        },
        saveUserPref() {
            this.updateLs({'infoSidebar': this.infoSidebar})
        },

        // other
        getCacheName(file_name) {
            let str = this.cacheName == 'root_'
                ? file_name == 'root_'
                    ? file_name
                    : `/${file_name}`
                : this.cacheName == file_name
                    ? `/${file_name}`
                    : `${this.cacheName}/${file_name}`

            return this.clearDblSlash(str)
        }
    },
    computed: {
        CDBN() {
            return 'ctf0-Media_Manager'
        },
        cacheName() {
            let folders = this.folders

            return folders.length ? this.clearDblSlash(`/${folders.join('/')}`) : 'root_'
        }
    }
}
