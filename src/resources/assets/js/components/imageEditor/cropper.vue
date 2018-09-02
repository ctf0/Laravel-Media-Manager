<template>
    <div class="card __cropper">

        <!-- effects -->
        <div class="top">
            <div v-if="imageCaman" class="__cropper-top-toolbar">
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
        </div>

        <div class="mid">
            <!-- controls -->
            <div v-if="imageCropper" class="__cropper-side-toolbar">
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

            <!-- img -->
            <div class="card-image">
                <figure class="image">
                    <img id="cropper" :src="url" crossOrigin>
                </figure>
            </div>
        </div>

        <!-- operations -->
        <div class="bottom">
            <div v-if="imageCropper" class="__cropper-bottom-toolbar">
                <!-- reset -->
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

<style lang="scss">
    .cropper-line {
        background-color: transparent !important;
    }

    .cropper-point {
        z-index: 1;

        &.point-e,
        &.point-w,
        &.point-s,
        &.point-n {
            background-color: transparent !important;
        }

        &.point-ne,
        &.point-se,
        &.point-nw,
        &.point-sw {
            width: 20px;
            height: 20px;
            border-style: solid;
            border-color: white;
            background-color: transparent !important;
        }

        &.point-ne {
            border-width: 3px 3px 0 0;
        }

        &.point-nw {
            border-width: 3px 0 0 3px;
        }

        &.point-se {
            border-width: 0 3px 3px 0;
        }

        &.point-sw {
            border-width: 0 0 3px 3px;
        }
    }
</style>

<script>
import Filters from './filters.vue'
import Cropper from 'cropperjs'
import omit from 'lodash/omit'

export default {
    components: {Filters},
    props: ['url', 'translations', 'route'],
    data() {
        return {
            imageCaman: null,
            imageCropper: null,
            dragMode: 'crop',
            hasChanged: false,
            croppedByUser: false,
            hasChangedByFilter: false,
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
        window.addEventListener('dblclick', (e) => {
            this.dragMode = e.target.dataset.cropperAction || this.dragMode
        })
    },
    mounted() {
        this.camanStart()
    },
    methods: {
        camanStart() {
            this.imageCaman = Caman('#cropper', () => {
                this.cropperStart()
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
                    break
                case 'zoom-out':
                    cropper.zoom(-0.1)
                    return this.hasChanged = true
                    break
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
                cropper.cropped ||
                this.hasChangedByFilter
                    ? true
                    : false
        },
        resetAll() {
            this.$nextTick(() => {
                let vm = this
                let cropper = vm.imageCropper
                let caman = vm.imageCaman

                vm.dragMode = cropper.options.dragMode
                vm.hasChanged = false
                vm.croppedByUser = false
                vm.hasChangedByFilter = false
                vm.reset = true
                vm.camanFilters = {}

                cropper.reset() // position
                cropper.clear() // selection
                cropper.setDragMode(vm.dragMode) // active btn

                caman.reset()
                caman.render(function() {
                    cropper.replace(this.toBase64(), true) // image

                    vm.$nextTick(() => {
                        vm.reset = false
                    })
                })
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
        },

        // utils
        dragModeIs(val) {
            return this.dragMode == val
        },
        trans(val) {
            return this.translations[val]
        },

        // filters
        updateFilter(name, val) {
            this.processing = true
            this.hasChangedByFilter = true
            let cropper = this.imageCropper
            let caman = this.imageCaman

            // make "greyscale/invert" switchable
            if (['greyscale', 'invert'].indexOf(name) >= 0 && this.camanFilters.hasOwnProperty(name)) {
                this.camanFilters = omit(this.camanFilters, name)
            } else {
                this.camanFilters[name] = val
            }

            // reset prev
            caman.revert(false)

            // re-apply all filters
            for (let name in this.camanFilters) {
                caman[name](this.camanFilters[name])
            }

            // render result
            caman.render(function() {
                cropper.replace(this.toBase64(), true)
            })
        }
    },
    watch: {
        hasChangedByFilter(val) {
            if (val == true) {
                this.hasChanged = true
            }
        }
    }
}
</script>
