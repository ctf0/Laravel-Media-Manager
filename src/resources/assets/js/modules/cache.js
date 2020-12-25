const DbWorker = new Worker(
    new URL('../webworkers/db.js', import.meta.url),
    {
        name: 'db'
        /* webpackEntryOptions: { filename: "workers/[name].js" } */
    }
)

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
            this.$ls.remove(this.CDBN)
        },
        preSaved() {
            let ls = this.getLs()

            if (Object.keys(ls).length) {
                this.useRandomNamesForUpload = ls.useRandomNamesForUpload || false
                this.folders = ls.folders || []
                this.toolBar = ls.toolBar || true
                this.selectedFile = ls.selectedFileName || null
                this.dirBookmarks = ls.dirBookmarks || []
            }
        },
        saveUserPref() {
            this.updateLs({'infoSidebar': this.infoSidebar})
        },

        /*                idb                */
        db(type, key = null, val = null) {
            return new Promise((resolve) => {
                DbWorker.onmessage = (e) => resolve(e.data)
                DbWorker.postMessage({
                    type : type, // get,set,del,clr,keys
                    key  : key ? encodeURI(key) : null,
                    val  : val
                })
            }).then((data) => data)
        }
    },
    computed: {
        CDBN() {
            return 'ctf0-Media_Manager'
        }
    }
}
