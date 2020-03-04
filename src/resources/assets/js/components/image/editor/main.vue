<template>
    <div ref="editor"
         class="card __editor">
        <!-- btns -->
        <div class="top">
            <div class="__top-toolbar">
                <section class="left">
                    <!-- filters -->
                    <filters v-if="!showDiff"
                             :reset="!haveFilters()"
                             :apply-filter="applyFilter"
                             :processing="processing"
                             :caman-filters="camanFilters"
                             class="__left-index"/>
                </section>

                <div class="right">
                    <!-- diff toggle -->
                    <button v-tippy="{arrow: true, theme: 'mm'}"
                            :disabled="(processing && !imageDiffIsReady) || diffDisable"
                            :class="{'is-active': showDiff}"
                            :title="trans('diff')"
                            class="btn-plain"
                            @click.stop="toggleDiff()">
                        <span class="icon"><icon name="code"/></span>
                    </button>

                    <!-- reset filters -->
                    <button v-tippy="{arrow: true, theme: 'mm'}"
                            :disabled="processing || !haveFilters()"
                            :title="trans('crop_reset_filters')"
                            class="btn-plain"
                            @click.stop="resetFilters()">
                        <span class="icon">
                            <icon :name="processing ? 'spinner' : 'times'"
                                  :pulse="processing"/>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <div class="mid">
            <!-- controls -->
            <controls :style="hiddenBtns"
                      :drag-mode-is="dragModeIs"
                      :trans="trans"
                      :operations="operations"
                      :processing="processing"
                      class="__side-toolbar"/>

            <div class="__cropper">
                <!-- img -->
                <figure :style="{'opacity': processing ? 0 : 1}"
                        class="image">
                    <img id="cropper"
                         :src="url"
                         crossOrigin>
                </figure>

                <!-- loading -->
                <div v-show="processing"
                     class="__loader">
                    <div class="ball-grid-pulse">
                        <div/><div/><div/><div/><div/><div/><div/><div/><div/>
                    </div>
                </div>

                <!-- diff -->
                <image-compare v-if="imageDiffIsReady"
                               :after="diffOriginal"
                               :before="diffCurrent"
                               :is-zoomable="true"
                               :is-draggable="true"
                               :zoom="{min: 1, max: 15}"
                               class="__diff is-draggable"
                               @movment="onDiffDrag">
                    <icon slot="icon-left"
                          name="arrow-left"/>
                    <icon slot="icon-right"
                          name="arrow-right"/>
                </image-compare>

                <!-- presets -->
                <presets :style="hiddenBtns"
                         :processing="processing"
                         :caman-filters="camanFilters"
                         :apply-filter="applyFilter"
                         :trans="trans"
                         class="__caman-presets"/>

                <!-- operations -->
                <div :style="hiddenBtns"
                     class="__bottom-toolbar">
                    <!-- reset everything -->
                    <button v-tippy="{arrow: true, theme: 'mm'}"
                            :disabled="processing || !hasChanged"
                            :title="trans('crop_reset')"
                            class="btn-plain"
                            @click.stop="operations('reset')">
                        <span class="icon">
                            <icon :name="processing ? 'spinner' : 'times'"
                                  :pulse="processing"/>
                        </span>
                    </button>

                    <!-- clear -->
                    <button v-tippy="{arrow: true, theme: 'mm'}"
                            :disabled="processing || !croppedByUser"
                            :title="trans('clear')"
                            class="btn-plain"
                            @click.stop="operations('clear')">
                        <span class="icon">
                            <icon :name="processing ? 'spinner' : 'ban'"
                                  :pulse="processing"/>
                        </span>
                    </button>

                    <!-- apply -->
                    <button v-tippy="{arrow: true, theme: 'mm'}"
                            :disabled="processing || !hasChanged"
                            :title="trans('crop_apply')"
                            class="btn-plain"
                            @click.stop="applyChanges()">
                        <span class="icon">
                            <icon :name="processing ? 'spinner' : 'check'"
                                  :pulse="processing"/>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" src="../../../../sass/modules/image-editor.scss"></style>

<script>
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'
import cloneDeep from 'lodash/cloneDeep'
import Cropper from 'cropperjs'

export default {
    components: {
        filters: require('./filters/index.vue').default,
        presets: require('./filters/presets.vue').default,
        controls: require('./controls.vue').default,
        imageCompare: require('vue-image-compare2').default
    },
    props: [
        'file',
        'noScroll',
        'trans',
        'route'
    ],
    data() {
        return {
            url: this.file.path,
            imageCropper: null,
            dragMode: 'move',
            rotation: 45,
            croppedByUser: false,
            initData: null,

            diffOriginal: null,
            diffCurrent: null,
            showDiff: false,
            diffDisable: true,

            hasChanged: false,
            processing: false,
            reset: false,
            imageCaman: null,
            camanFilters: {}
        }
    },
    // make cropperjs rotation follow the new value
    computed: {
        angles() {
            return 360 / this.rotation
        },
        ranges() {
            let final = []
            let list = Array.from(Array(this.angles).keys())
            list.shift() // remove 0

            list.forEach((item) => {
                let res = item * this.rotation
                res == 180 ? false : final.push(-res, res)
            })

            return final
        },
        imageDiffIsReady() {
            return Boolean(
                this.showDiff &&
                this.diffCurrent &&
                this.diffOriginal
            )
        },
        hiddenBtns() {
            if (this.showDiff) {
                return {
                    opacity: 0,
                    visibility: 'hidden',
                    'pointer-events': 'none'
                }
            }

            return {}
        }
    },
    created() {
        this.noScroll('add')
        window.addEventListener('dblclick', this.onDblClick)
    },
    mounted() {
        this.processing = true
        setTimeout(this.camanStart, 500)
    },
    beforeDestroy() {
        window.removeEventListener('dblclick', this.onDblClick)
        this.imageCropper.destroy()
        this.noScroll('remove')
    },
    methods: {
        // init
        camanStart() {
            this.imageCaman = Caman('#cropper', () => {
                this.cropperStart()
            })

            Caman.Event.listen('processStart', () => {
                this.processing = true
            })

            Caman.Event.listen('renderFinished', () => {
                this.processing = false
            })
        },
        cropperStart() {
            let image = document.querySelector('#cropper')

            this.imageCropper = new Cropper(image, {
                viewMode: 1,
                dragMode: this.dragMode,
                guides: false,
                highlight: false,
                autoCrop: false,
                toggleDragModeOnDblclick: true,
                responsive: false
            })

            image.addEventListener('ready', () => {
                this.processing = false
                if (!this.initData) {
                    return this.initData = this.imageCropper.getCroppedCanvas().toDataURL()
                }
            })

            image.addEventListener('cropmove', (e) => {
                EventHub.fire('stopHammerPropagate')

                switch (e.detail.action) {
                    case 'move':
                        break
                    case 'crop':
                        this.croppedByUser = true
                        this.hasChanged = true
                        break
                    case 'zoom':
                        this.hasChanged = true
                        break
                }
            })

            image.addEventListener('zoom', (e) => {
                this.hasChanged = true
            })
        },

        // operations
        operations(action) {
            let cropper = this.imageCropper
            let getData = cropper.getData()

            switch (action) {
                case 'move':
                case 'crop':
                    this.dragMode = action
                    cropper.setDragMode(action)
                    break
                case 'zoom-in':
                    cropper.zoom(0.1)

                    return this.hasChanged = true
                case 'zoom-out':
                    cropper.zoom(-0.1)

                    return this.hasChanged = true
                case 'rotate-left':
                    cropper.rotate(-this.rotation)
                    break
                case 'rotate-right':
                    cropper.rotate(this.rotation)
                    break
                case 'flip-horizontal':
                    this.ranges.includes(getData.rotate)
                        ? cropper.scaleY(-getData.scaleY)
                        : cropper.scaleX(-getData.scaleX)
                    break
                case 'flip-vertical':
                    this.ranges.includes(getData.rotate)
                        ? cropper.scaleX(-getData.scaleX)
                        : cropper.scaleY(-getData.scaleY)
                    break
                case 'reset':
                    this.resetAll()
                    break
                case 'clear':
                    this.croppedByUser = false
                    cropper.clear()
                    break
            }

            this.checkForChanges()
        },
        checkForChanges() {
            let cropper = this.imageCropper
            let getData = cropper.getData()

            this.hasChanged = (
                getData.rotate != 0 ||
                getData.scaleX != 1 ||
                getData.scaleY != 1 ||
                cropper.cropped ||
                this.haveFilters()
            ) ? true : false
        },
        resetAll() {
            this.$nextTick(() => {
                let cropper = this.imageCropper

                this.dragMode = cropper.options.dragMode
                this.hasChanged = false
                this.croppedByUser = false
                this.reset = true
                this.diffDisable = true

                cropper.reset() // position, rotation, flip, zoom
                cropper.clear() // selection
                cropper.setDragMode(this.dragMode) // active btn
                this.resetFilters()

                this.$nextTick(() => this.reset = false)
            })
        },
        clearSelection() {
            if (!this.croppedByUser) {
                this.imageCropper.clear()
            }
        },

        // filters
        resetFilters() {
            this.diffDisable = true
            this.camanFilters = {}
            this.imageCaman.reset()
            this.imageCropper.replace(this.initData, true) // init
        },
        haveFilters() {
            return !isEmpty(this.camanFilters)
        },
        applyFilter(name, val) {
            this.hasChanged = true

            let filters = this.camanFilters
            let caman = this.imageCaman

            // make nullable filters switchable
            if (!val && filters.hasOwnProperty(name)) {
                filters = this.camanFilters = omit(filters, name)
            } else {
                filters[name] = val
            }

            // reset prev
            caman.reset()

            // re-apply all filters
            for (let name in filters) {
                caman[name](filters[name])
            }

            // render result
            this.renderImage()
        },
        renderImage() {
            let caman = this.imageCaman
            let cropper = this.imageCropper

            return caman.render(function() {
                cropper.replace(this.toBase64(), true)
            })
        },

        // save
        getCropperData(cropper = this.imageCropper) {
            let type = this.file.type

            // has the user made a crop selection ?
            if (!this.croppedByUser) {
                cropper.crop()
                cropper.setCropBoxData({
                    height: cropper.getContainerData().height,
                    left: 0,
                    top: 0,
                    width: cropper.getContainerData().width
                })
            }

            let url = cropper.getCroppedCanvas({
                fillColor: type.includes('png') ? 'transparent' : '#fff',
                imageSmoothingQuality: 'high'
            }).toDataURL(type)

            // reset the auto crop selection we made
            this.$nextTick(() => this.clearSelection())

            return url
        },
        applyChanges() {
            this.saveToDisk(this.getCropperData())
        },
        saveToDisk(data) {
            const parent = this.$parent
            parent.toggleLoading()
            parent.showNotif(trans('stand_by'), 'info')

            axios.post(this.route, {
                data: data,
                path: parent.files.path,
                name: this.file.name,
                mime_type: this.file.type
            }).then(({data}) => {
                parent.toggleLoading()
                data.success
                    ? EventHub.fire('image-edited', data.message) // notify parent to refresh on finish
                    : parent.showNotif(data.message, 'danger')

            }).catch((err) => {
                console.error(err)
                parent.ajaxError()
            })
        },

        // diff
        toggleDiff() {
            this.showDiff = !this.showDiff
        },
        renderDiff() {
            // current
            this.diffCurrent = this.getCropperData()

            // original
            /**
             * we need to use the cloneDeep to make sure
             * we don't apply the diff operations to the original cropper,
             * otherwise we wont be able to reset to the original image without extra work
             */
            let cropperClone = cloneDeep(this.imageCropper)
            let getData = cropperClone.getData()
            let x = getData.scaleX
            let y = getData.scaleY
            cropperClone.setData({
                rotate: 0, // reset rotation
                scaleX: x < 1 ? -x : x, // reset flip-horizontal
                scaleY: y < 1 ? -y : y // reset flip-vertical
            })

            cropperClone.replace(this.initData, true)

            // make sure the returned original render is correct
            setTimeout(() => {
                this.diffOriginal = this.getCropperData(cropperClone)
            }, 100)
        },

        // utils
        dragModeIs(val) {
            return this.dragMode == val
        },
        onDblClick(e) {
            this.dragMode = e.target.dataset.cropperAction || this.dragMode
        },
        onDiffDrag() {
            EventHub.fire('stopHammerPropagate')
        }
    },
    watch: {
        hasChanged(val) {
            if (val) {
                this.diffDisable = false
            }
        },
        showDiff(val) {
            if (val) {
                this.processing = true // hide main canvas
                this.renderDiff()
            } else {
                /**
                 * when we replaced the cropperClone with the original data
                 * cropper internally replaced the working canvas src which
                 * has removed the caman render so we just need to reapply it again
                 * after closing the diff panel
                 *
                 * this has no penalties on performance as the data is already saved
                 * from the last time the effect was applied
                 */
                this.renderImage()

                this.processing = false
                this.diffOriginal = null
                this.diffCurrent = null
                this.$refs.editor.style.marginTop = ''
            }
        },
        imageDiffIsReady(val) {
            if (val) {
                let t = setInterval(() => {
                    let diff = document.querySelector('.__diff').offsetHeight
                    let canvas = document.querySelector('.__cropper').offsetHeight

                    if (diff) {
                        this.$refs.editor.style.marginTop = diff > canvas
                            ? `-${(diff - canvas)}px`
                            : `-${(canvas - diff)}px`

                        clearInterval(t)
                    }
                }, 50)
            }
        }
    }
}
</script>
