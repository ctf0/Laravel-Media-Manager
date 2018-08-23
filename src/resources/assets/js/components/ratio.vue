<template>
    <div class="progress">
        <p v-tippy="{theme: 'light', arrow: true}"
           v-for="(val, key) in contentRatio"
           :class="`is-${key}`"
           :style="getRatio(val)"
           :title="getToolTipContent(key, val)"
           class="progress-bar progress-bar-striped active">
            <strong v-show="val > 0">{{ key }}</strong>
        </p>
    </div>
</template>

<style scoped lang="scss">
    .progress-bar {
        box-shadow: none;

        strong {
            text-transform: uppercase;
            mix-blend-mode: difference;
        }
    }
</style>

<script>
export default {
    props: {
        list: {
            type: Array,
            required: true
        },
        total: {
            type: Number,
            required: true
        }
    },
    computed: {
        parent() {
            return this.$parent
        },
        contentRatio() {
            let ratio = {
                audio: 0,
                video: 0,
                image: 0,
                text: 0,
                folder: 0,
                application: 0,
                pdf: 0
            }

            this.list.forEach((e) => {
                if (this.parent.fileTypeIs(e, 'audio')) ratio.audio++
                if (this.parent.fileTypeIs(e, 'video')) ratio.video++
                if (this.parent.fileTypeIs(e, 'image')) ratio.image++
                if (this.parent.fileTypeIs(e, 'text')) ratio.text++
                if (this.parent.fileTypeIs(e, 'pdf')) ratio.pdf++
                if (this.parent.fileTypeIs(e, 'folder')) ratio.folder++
                if (this.parent.fileTypeIs(e, 'application')) ratio.application++
            })

            return ratio
        }
    },
    methods: {
        getRatio(val) {
            return {
                width: `${val / this.total * 100}%`
            }
        },
        getToolTipContent(k, v) {
            return `<p class="title is-marginless">${v}</p><p class="heading">${k}</p>`
        }
    }
}
</script>
