<template>
    <div v-if="showPanel"
         class="modal mm-animated fadeIn is-active usage-intro-panel">
        <div class="modal-background gradient-pattern"/>
        <div class="modal-content">
            <div class="__choices">
                <div class="left"
                     @click.stop="showIntro('touch')">
                    <span class="icon"><icon name="mobile"
                                             scale="15"/></span>
                </div>
                <div class="mid">
                    <div class="splitter"/>
                </div>
                <div class="right"
                     @click.stop="showIntro('desktop')">
                    <span class="icon"><icon name="desktop"
                                             scale="15"/></span>
                </div>
            </div>
        </div>

        <div class="__corner">
            <p>MANAGER\\SHORTCUTS</p>
        </div>
        <button class="modal-close is-large"
                @click.stop="closePanel()"/>
    </div>
</template>

<script>
import panels from '../../mixins/panels'

export default {
    mixins: [panels],
    props: ['trans', 'noScroll'],
    data() {
        return {
            type: null
        }
    },
    methods: {
        eventsListener() {
            EventHub.listen('show-usage-intro', () => {
                this.showPanel = true
            })
        },
        closePanel() {
            this.showPanel = false
        },
        showIntro(type) {
            this.type = type
        }
    },
    watch: {
        showPanel(val) {
            this.showPanelWatcher(val)
        }
    }
}
</script>
