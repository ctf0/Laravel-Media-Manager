<template>
    <div class="progress">
        <p v-tippy="{theme: 'light', arrow: true, followCursor: true}"
           v-for="(val, key) in contentRatio"
           :class="`is-${key}`"
           :style="getRatio(val)"
           :title="getToolTipContent(key, val)"
           :key="key"
           class="progress-bar progress-bar-striped active">
            <strong v-show="val > 0">{{ key }}</strong>
        </p>
    </div>
</template>

<style scoped lang="scss">
    .progress-bar {
        box-shadow: none;

        strong {
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: uppercase;
            mix-blend-mode: difference;
        }
    }
</style>

<script>
export default {
    props: ['list', 'total', 'fileTypeIs'],
    computed: {
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
                if (this.fileTypeIs(e, 'audio')) ratio.audio++
                if (this.fileTypeIs(e, 'video')) ratio.video++
                if (this.fileTypeIs(e, 'image')) ratio.image++
                if (this.fileTypeIs(e, 'text')) ratio.text++
                if (this.fileTypeIs(e, 'pdf')) ratio.pdf++
                if (this.fileTypeIs(e, 'folder')) ratio.folder++
                if (this.fileTypeIs(e, 'application')) ratio.application++
            })

            return ratio
        }
    },
    methods: {
        getRatio(val) {
            return {
                width: this.calcRatio(val)
            }
        },
        calcRatio(val) {
            return (val / this.total * 100).toFixed(2) + '%'
        },
        getToolTipContent(k, v) {
            return `<p class="title is-marginless">${v}</p><p class="heading">${k}</p>`
        }
    }
}
</script>
