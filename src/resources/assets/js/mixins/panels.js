export default {
    data() {
        return {
            showPanel: false
        }
    },
    created() {
        this.eventsListener()
    },
    methods: {
        shortCut(e) {
            if (keycode(e) == 'esc') {
                this.closePanel()
            }
        },
        showPanelWatcher(val) {
            if (val) {
                this.noScroll('add')
                EventHub.fire('disable-global-keys', true)
                document.addEventListener('keydown', this.shortCut)
            } else {
                EventHub.fire('disable-global-keys', false)
                document.removeEventListener('keydown', this.shortCut)
                this.noScroll('remove')
            }
        }
    }
}
