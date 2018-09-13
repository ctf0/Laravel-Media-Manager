<template>
    <div v-if="showPanel" class="modal mm-animated fadeIn is-active">
        <div class="modal-background"/>
        <div class="modal-content">
            <!-- TODO -->
        </div>

        <div class="modal-bottom-side">
            <p>MANAGER\\SHORTCUTS</p>
        </div>
        <button class="modal-close is-large" @click="closePanel()"/>
    </div>
</template>

<style scoped lang="scss">
    @import '../../../sass/partials/vars';
    @import '../../../sass/partials/panels';

    .gradient-pattern {
        background: radial-gradient(rgba(white, 0.02) 15%, transparent 16%), rgba($dark, 0.98);
        background-position: 10px 10px;
        background-origin: padding-box;
        background-clip: border-box;
        background-size: 25px 25px;
    }

    .modal-bottom-side {
        font: normal 100%/normal Arial, Helvetica, sans-serif;
        position: absolute;
        bottom: 0;
        margin-bottom: 1rem;
        right: 0;
        width: 8%;
        font-size: 3.5rem;
        line-height: 0.85;
        font-weight: bold;
        word-break: break-all;
        color: black;

        p {
            color: black;
        }

        &::before,
        &::after {
            content: '';
            position: absolute;
            bottom: 0;
        }

        &::after {
            height: 150%;
            left: -3rem;
            margin-bottom: -1rem;
            width: 5px;
            background: black;
            transform: skew(-40deg);
            transform-origin: left bottom;
        }

        &::before {
            height: 5px;
            margin-bottom: -1rem;
            left: 0;
            width: 45%;
            background: $green;
        }
    }
</style>

<script>
import panels from '../../mixins/panels'

export default {
    mixins: [panels],
    props: ['trans', 'noScroll'],
    methods: {
        eventsListener() {
            EventHub.listen('show-usage-intro', () => {
                this.showPanel = true
            })
        },
        closePanel() {
            this.showPanel = false
        }
    },
    watch: {
        showPanel(val) {
            this.showPanelWatcher(val)
        }
    }
}
</script>
