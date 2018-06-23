<template>
    <div class="card __cropper">

        <!-- effects -->
        <div class="top">
            <div v-if="imageCaman" class="__cropper-top-toolbar">
                <camann :step="10" :min="-100" :max="100"
                        :reset="reset" :processing="processing"
                        icon="sun-o" filter-name="brightness"/>
                <camann :step="10" :min="-100" :max="100"
                        :reset="reset" :processing="processing"
                        icon="adjust" filter-name="contrast"/>
                <camann :step="10" :min="-100" :max="100"
                        :reset="reset" :processing="processing"
                        icon="eye-slash" filter-name="saturation"/>
                <camann :step="10" :min="-100" :max="100"
                        :reset="reset" :processing="processing"
                        icon="flash" filter-name="vibrance"/>
                <camann :step="10" :min="-100" :max="100"
                        :reset="reset" :processing="processing"
                        icon="thermometer-half" filter-name="exposure"/>
                <camann :step="5" :min="0" :max="100"
                        :reset="reset" :processing="processing"
                        icon="eyedropper" filter-name="hue"/>
                <camann :step="5" :min="0" :max="100"
                        :reset="reset" :processing="processing"
                        icon="lemon-o" filter-name="sepia"/>
                <camann :step="0.1" :min="0" :max="10"
                        :reset="reset" :processing="processing"
                        icon="flask" filter-name="gamma"/>
                <camann :step="5" :min="0" :max="100"
                        :reset="reset" :processing="processing"
                        icon="dot-circle-o" filter-name="noise"/>
                <camann :step="5" :min="0" :max="100"
                        :reset="reset" :processing="processing"
                        icon="scissors" filter-name="clip"/>
                <camann :step="5" :min="0" :max="100"
                        :reset="reset" :processing="processing"
                        icon="diamond" filter-name="sharpen"/>
                <camann :step="1" :min="0" :max="20"
                        :reset="reset" :processing="processing"
                        icon="filter" filter-name="stackBlur"/>
                <camann :reset="reset" :processing="processing"
                        icon="shield" filter-name="greyscale"/>
                <camann :reset="reset" :processing="processing"
                        icon="cube" filter-name="invert"/>
            </div>
        </div>

        <div class="mid">
            <!-- controls -->
            <div v-if="imageCropper" class="__cropper-side-toolbar">
                <button v-tippy
                        :class="{'is-active': dragModeIs('move')}"
                        :disabled="processing"
                        :title="trans('move')"
                        class="btn-plain" @click="Ops('move')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'arrows'" :pulse="processing"/></span>
                </button>
                <button v-tippy
                        :class="{'is-active': dragModeIs('crop')}"
                        :disabled="processing"
                        :title="trans('crop')"
                        class="btn-plain" @click="Ops('crop')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'crop'" :pulse="processing"/></span>
                </button>
                <button v-tippy
                        :disabled="processing"
                        :title="trans('crop_zoom_in')"
                        class="btn-plain" @click="Ops('zoom-in')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'search-plus'" :pulse="processing"/></span>
                </button>
                <button v-tippy
                        :disabled="processing"
                        :title="trans('crop_zoom_out')"
                        class="btn-plain" @click="Ops('zoom-out')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'search-minus'" :pulse="processing"/></span>
                </button>
                <button v-tippy
                        :disabled="processing"
                        :title="trans('crop_rotate_left')"
                        class="btn-plain" @click="Ops('rotate-left')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'rotate-left'" :pulse="processing"/></span>
                </button>
                <button v-tippy
                        :disabled="processing"
                        :title="trans('crop_rotate_right')"
                        class="btn-plain" @click="Ops('rotate-right')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'rotate-right'" :pulse="processing"/></span>
                </button>
                <button v-tippy
                        :disabled="processing"
                        :title="trans('crop_flip_horizontal')"
                        class="btn-plain" @click="Ops('flip-horizontal')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'arrows-h'" :pulse="processing"/></span>
                </button>
                <button v-tippy
                        :disabled="processing"
                        :title="trans('crop_flip_vertical')"
                        class="btn-plain" @click="Ops('flip-vertical')">
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

        <div class="bottom">
            <div v-if="imageCropper" class="__cropper-bottom-toolbar">
                <!-- reset -->
                <button v-tippy
                        :disabled="processing"
                        :title="trans('crop_reset')"
                        class="btn-plain" @click="Ops('reset')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'times'" :pulse="processing"/></span>
                </button>
                <!-- clear -->
                <button v-tippy
                        :disabled="processing || !imageCropper.cropped"
                        :title="trans('clear')"
                        class="btn-plain" @click="Ops('clear')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'ban'" :pulse="processing"/></span>
                </button>
                <!-- apply -->
                <button v-tippy
                        :disabled="processing || !hasChanged"
                        :title="trans('crop_apply')"
                        class="btn-plain" @click="applyChanges()">
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
import Camann from './caman.vue'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'

export default {
    components: {Camann},
    props: ['url', 'translations', 'route'],
    data() {
        return {
            imageCropper: null,
            imageCaman: null,
            dragMode: 'crop',
            hasChanged: false,
            hasChangedByFilter: false,
            processing: false,
            reset: false
        }
    },

    mounted() {
        this.camanStart()
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
            let image = document.getElementById('cropper')
            let vm = this

            this.imageCropper = new Cropper(image, {
                viewMode: 1,
                dragMode: this.dragMode,
                guides: false,
                highlight: false,
                autoCrop: false,
                toggleDragModeOnDblclick: true, // we cant catch the dragMode changes
                responsive: true,
                cropmove(e) {
                    if (e.detail.action == 'move') {
                        return vm.hasChanged = vm.imageCropper.cropped ? true : false
                    }

                    vm.hasChanged = true
                }
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
                    break
                case 'zoom-out':
                    cropper.zoom(-0.1)
                    break
                case 'rotate-left':
                    cropper.rotate(-this.rotation)
                    break
                case 'rotate-right':
                    cropper.rotate(this.rotation)
                    break
                case 'flip-horizontal':
                    if (this.ranges.includes(getData.rotate)) {
                        return cropper.scaleY(-getData.scaleY)
                    }

                    cropper.scaleX(-getData.scaleX)
                    break
                case 'flip-vertical':
                    if (this.ranges.includes(getData.rotate)) {
                        return cropper.scaleX(-getData.scaleX)
                    }

                    cropper.scaleY(-getData.scaleY)
                    break

                case 'reset':
                    this.resetAll()
                    break
                case 'clear':
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
                vm.hasChangedByFilter = false
                vm.reset = true

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

            axios.post(this.route, {
                path: parent.files.path,
                data: data,
                name: name
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

            // val ? caman.revert(false) : false

            caman[name](val).render(function() {
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
