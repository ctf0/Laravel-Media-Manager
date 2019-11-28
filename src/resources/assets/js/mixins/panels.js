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
        showPanelWatcher(val) {
            if (val) {
                this.noScroll('add')
                EventHub.fire('disable-global-keys', true)
            } else {
                EventHub.fire('disable-global-keys', false)
                this.noScroll('remove')
            }
        }
    }
}
