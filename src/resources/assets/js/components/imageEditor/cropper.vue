<template>
    <div class="card __cropper">

        <!-- effects -->
        <div class="top">
            <div v-if="imageCaman" class="__top-toolbar">
                <div class="left">
                    <filters :step="10" :min="-100" :max="100"
                             :reset="reset" :processing="processing"
                             icon="sun-o" filter-name="brightness"/>
                    <filters :step="10" :min="-100" :max="100"
                             :reset="reset" :processing="processing"
                             icon="adjust" filter-name="contrast"/>
                    <filters :step="10" :min="-100" :max="100"
                             :reset="reset" :processing="processing"
                             icon="eye-slash" filter-name="saturation"/>
                    <filters :step="10" :min="-100" :max="100"
                             :reset="reset" :processing="processing"
                             icon="flash" filter-name="vibrance"/>
                    <filters :step="10" :min="-100" :max="100"
                             :reset="reset" :processing="processing"
                             icon="thermometer-half" filter-name="exposure"/>
                    <filters :step="5" :min="0" :max="100"
                             :reset="reset" :processing="processing"
                             icon="eyedropper" filter-name="hue"/>
                    <filters :step="5" :min="0" :max="100"
                             :reset="reset" :processing="processing"
                             icon="lemon-o" filter-name="sepia"/>
                    <filters :step="0.1" :min="0" :max="10"
                             :reset="reset" :processing="processing"
                             icon="flask" filter-name="gamma"/>
                    <filters :step="5" :min="0" :max="100"
                             :reset="reset" :processing="processing"
                             icon="dot-circle-o" filter-name="noise"/>
                    <filters :step="5" :min="0" :max="100"
                             :reset="reset" :processing="processing"
                             icon="scissors" filter-name="clip"/>
                    <filters :step="5" :min="0" :max="100"
                             :reset="reset" :processing="processing"
                             icon="diamond" filter-name="sharpen"/>
                    <filters :step="1" :min="0" :max="20"
                             :reset="reset" :processing="processing"
                             icon="filter" filter-name="stackBlur"/>
                    <filters :reset="reset" :processing="processing"
                             icon="shield" filter-name="greyscale"/>
                    <filters :reset="reset" :processing="processing"
                             icon="cube" filter-name="invert"/>
                </div>

                <div class="right">
                    <!-- reset filters -->
                    <button v-tippy="{arrow: true, theme: 'light'}"
                            :disabled="processing || haveFilters()"
                            :title="trans('crop_reset_filters')"
                            class="btn-plain"
                            @click="resetFilters()">
                        <span class="icon"><icon :name="processing ? 'spinner' : 'times'" :pulse="processing"/></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="mid">
            <!-- controls -->
            <div v-if="imageCropper" class="__side-toolbar">
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :class="{'is-active': dragModeIs('move')}"
                        :disabled="processing"
                        :title="trans('move')"
                        class="btn-plain"
                        @click="Ops('move')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'arrows'" :pulse="processing"/></span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :class="{'is-active': dragModeIs('crop')}"
                        :disabled="processing"
                        :title="trans('crop')"
                        class="btn-plain"
                        @click="Ops('crop')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'crop'" :pulse="processing"/></span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :title="trans('crop_zoom_in')"
                        class="btn-plain"
                        @click="Ops('zoom-in')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'search-plus'" :pulse="processing"/></span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :title="trans('crop_zoom_out')"
                        class="btn-plain"
                        @click="Ops('zoom-out')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'search-minus'" :pulse="processing"/></span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :title="trans('crop_rotate_left')"
                        class="btn-plain"
                        @click="Ops('rotate-left')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'rotate-left'" :pulse="processing"/></span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :title="trans('crop_rotate_right')"
                        class="btn-plain"
                        @click="Ops('rotate-right')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'rotate-right'" :pulse="processing"/></span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :title="trans('crop_flip_horizontal')"
                        class="btn-plain"
                        @click="Ops('flip-horizontal')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'arrows-h'" :pulse="processing"/></span>
                </button>
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing"
                        :title="trans('crop_flip_vertical')"
                        class="btn-plain"
                        @click="Ops('flip-vertical')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'arrows-v'" :pulse="processing"/></span>
                </button>
            </div>

            <div class="card-image">
                <!-- img -->
                <figure class="image">
                    <img id="cropper" :src="url" crossOrigin>
                </figure>

                <!-- presets -->
                <presets :image-cropper="imageCropper" :processing="processing"/>
            </div>
        </div>

        <!-- operations -->
        <div class="bottom">
            <div v-if="imageCropper" class="__bottom-toolbar">
                <!-- reset everything -->
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing || !hasChanged"
                        :title="trans('crop_reset')"
                        class="btn-plain"
                        @click="Ops('reset')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'times'" :pulse="processing"/></span>
                </button>
                <!-- clear -->
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing || !croppedByUser"
                        :title="trans('clear')"
                        class="btn-plain"
                        @click="Ops('clear')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'ban'" :pulse="processing"/></span>
                </button>
                <!-- apply -->
                <button v-tippy="{arrow: true, theme: 'light'}"
                        :disabled="processing || !hasChanged"
                        :title="trans('crop_apply')"
                        class="btn-plain"
                        @click="applyChanges()">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'check'" :pulse="processing"/></span>
                </button>
            </div>
        </div>

    </div>
</template>

<style lang="scss" src="../../../sass/modules/image-editor.scss"></style>

<script>
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import Filters from './filters.vue'
import Presets from './presets.vue'
import Cropper from 'cropperjs'

export default {
    components: {Filters, Presets},
    props: ['url', 'translations', 'route'],
    data() {
        return {
            imageCaman: null,
            imageCropper: null,
            dragMode: 'move',
            hasChanged: false,
            croppedByUser: false,
            processing: false,
            reset: false,
            camanFilters: {}
        }
    },
    // make cropperjs rotation follow the new value
    computed: {
        rotation() {
            return 45
        },
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
        }
    },
    created() {
        window.addEventListener('dblclick', this.ondblclick)
    },
    mounted() {
        this.camanStart()
    },
    beforeDestroy() {
        window.removeEventListener('dblclick', this.ondblclick)
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
                toggleDragModeOnDblclick: true, // we cant catch the dragMode changes
                responsive: true
            })

            image.addEventListener('cropmove', (e) => {
                let action = e.detail.action

                switch (action) {
                    case 'move':
                        EventHub.fire('stopHammerPropagate')
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
        Ops(action) {
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
                default:
            }

            this.checkForChanges()
        },
        checkForChanges() {
            let cropper = this.imageCropper
            let getData = cropper.getData()

            return this.hasChanged =
                getData.rotate != 0 ||
                getData.scaleX != 1 ||
                getData.scaleY != 1 ||
                cropper.cropped
                    ? true
                    : false
        },
        resetAll() {
            this.$nextTick(() => {
                let cropper = this.imageCropper

                this.dragMode = cropper.options.dragMode
                this.hasChanged = false
                this.croppedByUser = false
                this.reset = true

                cropper.reset() // position
                cropper.clear() // selection
                cropper.setDragMode(this.dragMode) // active btn

                if (this.haveFilters()) this.resetFilters()
                this.reset = false
            })
        },

        // filters
        resetFilters() {
            this.camanFilters = {}
            this.imageCaman.reset()
            this.renderImage()
            this.checkForChanges()
        },
        haveFilters() {
            return isEmpty(this.camanFilters)
        },

        // utils
        dragModeIs(val) {
            return this.dragMode == val
        },
        trans(val) {
            return this.translations[val]
        },
        ondblclick(e) {
            this.dragMode = e.target.dataset.cropperAction || this.dragMode
        },

        // filters
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
        applyChanges() {
            let cropper = this.imageCropper
            let file = this.$parent.selectedFile
            let type = file.type

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

            let data = cropper.getCroppedCanvas({
                fillColor: type.includes('png') ? 'transparent' : '#fff',
                imageSmoothingQuality: 'high'
            }).toDataURL(type)

            // cropper.replace(data)
            this.saveToDisk(data, file.name)
        },
        saveToDisk(data, name) {
            const parent = this.$parent
            parent.toggleLoading()
            parent.showNotif(parent.trans('stand_by'), 'info')

            axios.post(this.route, {
                path: parent.files.path,
                data: data,
                name: name
            }).then(({data}) => {

                parent.toggleLoading()
                data.success
                    ? EventHub.fire('image-edited', data.message) // notify parent to refresh on finish
                    : parent.showNotif(data.message, 'danger')

                // reset the auto crop selection we made
                if (!this.croppedByUser) {
                    this.imageCropper.clear()
                }

            }).catch((err) => {
                console.error(err)
                parent.ajaxError()
            })
        }
    }
}
</script>
