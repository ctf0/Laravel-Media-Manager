<template>
    <button v-if="isSupported"
            v-tippy="{arrow: true}"
            class="button"
            :title="run ? trans('voice_stop') : trans('voice_start')"
            @click.stop="toggle()">
        <span class="icon"
              :class="run ? 'has-text-danger' : 'has-text-link'">
            <icon name="microphone"/>
        </span>
    </button>
</template>

<script>
import annyang from 'annyang'

export default {
    props: ['trans', 'searchFor'],
    data() {
        return {
            run: false,
            isSupported: true,
            transcript: null
        }
    },
    created() {
        if (!annyang) {
            this.isSupported = false
            console.error('Speech Recognition is not supported')
        }
    },
    mounted() {
        annyang.addCallback('result', (phrases) => {
            this.$parent.searchFor = phrases[0]
        })
    },
    beforeDestroy() {
        annyang.removeCallback()
    },
    methods: {
        toggle() {
            return this.run ? this.stop() : this.start()
        },
        stop() {
            this.run = false
            annyang.abort()
        },
        start() {
            this.run = true
            annyang.start()

            // NOTE: testing
            // setTimeout(() => {
            //     annyang.trigger('Time for some thrilling heroics')
            // }, 1000)
        }
    },
    watch: {
        searchFor(val) {
            if (!val) {
                this.transcript = null
                this.stop()
            }
        }
    }
}
</script>
