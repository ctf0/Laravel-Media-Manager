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
                EventHub.fire('disable-global-keys', true)
                this.noScroll('add')
                document.addEventListener('keydown', this.shortCut)
            } else {
                EventHub.fire('disable-global-keys', false)
                document.removeEventListener('keydown', this.shortCut)
                this.noScroll('remove')
            }
        }
    }
}
