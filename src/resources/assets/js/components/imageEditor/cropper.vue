<template>
    <div class="card __cropper">

        <!-- effects -->
        <div class="top">
            <div class="__cropper-top-toolbar" v-if="imageCaman">
                <camann :step="10" :min="-100" :max="100"
                        icon="sun-o" filter-name="brightness"
                        :reset="reset" :processing="processing"/>
                <camann :step="10" :min="-100" :max="100"
                        icon="adjust" filter-name="contrast"
                        :reset="reset" :processing="processing"/>
                <camann :step="10" :min="-100" :max="100"
                        icon="eye-slash" filter-name="saturation"
                        :reset="reset" :processing="processing"/>
                <camann :step="10" :min="-100" :max="100"
                        icon="flash" filter-name="vibrance"
                        :reset="reset" :processing="processing"/>
                <camann :step="10" :min="-100" :max="100"
                        icon="thermometer-half" filter-name="exposure"
                        :reset="reset" :processing="processing"/>
                <camann :step="5" :min="0" :max="100"
                        icon="eyedropper" filter-name="hue"
                        :reset="reset" :processing="processing"/>
                <camann :step="5" :min="0" :max="100"
                        icon="lemon-o" filter-name="sepia"
                        :reset="reset" :processing="processing"/>
                <camann :step="0.1" :min="0" :max="10"
                        icon="flask" filter-name="gamma"
                        :reset="reset" :processing="processing"/>
                <camann :step="5" :min="0" :max="100"
                        icon="dot-circle-o" filter-name="noise"
                        :reset="reset" :processing="processing"/>
                <camann :step="5" :min="0" :max="100"
                        icon="scissors" filter-name="clip"
                        :reset="reset" :processing="processing"/>
                <camann :step="5" :min="0" :max="100"
                        icon="diamond" filter-name="sharpen"
                        :reset="reset" :processing="processing"/>
                <camann :step="1" :min="0" :max="20"
                        icon="filter" filter-name="stackBlur"
                        :reset="reset" :processing="processing"/>
                <camann icon="shield" filter-name="greyscale"
                        :reset="reset" :processing="processing"/>
                <camann icon="cube" filter-name="invert"
                        :reset="reset" :processing="processing"/>
            </div>
        </div>

        <div class="mid">
            <!-- controls -->
            <div class="__cropper-side-toolbar" v-if="imageCropper">
                <button class="btn-plain"
                        :class="{'is-active': dragModeIs('move')}"
                        :disabled="processing"
                        @click="Ops('move')"
                        v-tippy :title="trans('move')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'arrows'" :pulse="processing"/></span>
                </button>
                <button class="btn-plain"
                        :class="{'is-active': dragModeIs('crop')}"
                        :disabled="processing"
                        @click="Ops('crop')"
                        v-tippy :title="trans('crop')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'crop'" :pulse="processing"/></span>
                </button>
                <button class="btn-plain"
                        :disabled="processing"
                        @click="Ops('zoom-in')"
                        v-tippy :title="trans('crop_zoom_in')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'search-plus'" :pulse="processing"/></span>
                </button>
                <button class="btn-plain"
                        :disabled="processing"
                        @click="Ops('zoom-out')"
                        v-tippy :title="trans('crop_zoom_out')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'search-minus'" :pulse="processing"/></span>
                </button>
                <button class="btn-plain"
                        :disabled="processing"
                        @click="Ops('rotate-left')"
                        v-tippy :title="trans('crop_rotate_left')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'rotate-left'" :pulse="processing"/></span>
                </button>
                <button class="btn-plain"
                        :disabled="processing"
                        @click="Ops('rotate-right')"
                        v-tippy :title="trans('crop_rotate_right')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'rotate-right'" :pulse="processing"/></span>
                </button>
                <button class="btn-plain"
                        :disabled="processing"
                        @click="Ops('flip-horizontal')"
                        v-tippy :title="trans('crop_flip_horizontal')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'arrows-h'" :pulse="processing"/></span>
                </button>
                <button class="btn-plain"
                        :disabled="processing"
                        @click="Ops('flip-vertical')"
                        v-tippy :title="trans('crop_flip_vertical')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'arrows-v'" :pulse="processing"/></span>
                </button>
            </div>

            <!-- img -->
            <div class="card-image">
                <figure class="image">
                    <img :src="url" id="cropper" crossOrigin="anonymous">
                </figure>
            </div>
        </div>

        <div class="bottom">
            <div class="__cropper-bottom-toolbar" v-if="imageCropper">
                <!-- reset -->
                <button class="btn-plain"
                        :disabled="processing"
                        @click="Ops('reset')"
                        v-tippy :title="trans('crop_reset')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'times'" :pulse="processing"/></span>
                </button>
                <!-- clear -->
                <button class="btn-plain"
                        :disabled="processing || !imageCropper.cropped"
                        @click="Ops('clear')"
                        v-tippy :title="trans('clear')">
                    <span class="icon"><icon :name="processing ? 'spinner' : 'ban'" :pulse="processing"/></span>
                </button>
                <!-- apply -->
                <button class="btn-plain"
                        :disabled="processing || !hasChanged"
                        @click="applyChanges()"
                        v-tippy :title="trans('crop_apply')">
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
            this.$parent.toggleLoading()
            let files = this.$parent.files

            axios.post(this.route, {
                path: files.path ? files.path : '/',
                data: data,
                name: name
            }).then(({data}) => {

                this.$parent.toggleLoading()

                if (data.success) {
                    // notify parent to refresh on finish
                    EventHub.fire('image-edited')

                    this.$parent.$refs['success-audio'].play()
                    this.$parent.removeCachedResponse('../')
                    this.$parent.showNotif(`${this.trans('save_success')} "${data.message}"`)
                } else {
                    this.$parent.showNotif(data.message, 'danger')
                }

            }).catch((err) => {
                console.error(err)
                this.$parent.ajaxError()
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
