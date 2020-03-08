import debounce from 'lodash/debounce'
import Dropzone from 'dropzone'

export default {
    computed: {
        uploadPanelImg() {
            if (this.uploadArea) {
                let imgs = this.uploadPanelImgList
                let grds = this.uploadPanelGradients

                let url = imgs.length ? imgs[Math.floor(Math.random() * imgs.length)] : null
                let color = grds[Math.floor(Math.random() * grds.length)]

                return url
                    ? {'--gradient': color, 'background-image': `url("${url}")`}
                    : {'--gradient': color}
            }

            return {}
        },
        uploadPreviewListSize() {
            let size = this.uploadPreviewList
                    .map((el) => el.size)
                    .reduce((a, b) => a + b, 0)

            return size ? this.getFileSize(size) : 0
        }
    },
    methods: {
        // dropzone
        fileUpload() {
            let uploaded = 0
            let allFiles = 0
            let uploadProgress = 0

            let manager = this
            let queueFix = false
            let last = null
            let uploadPreview = '#uploadPreview'
            let uploadSize = this.getResrtictedUploadSize() || 256
            let uploadTypes = this.getResrtictedUploadTypes()?.join(',') || null
            let autoProcess = this.config.previewFilesBeforeUpload
                ? {
                    autoProcessQueue: false,
                    maxThumbnailFilesize: 25, // mb
                    createImageThumbnails: true,
                    addRemoveLinks: true,
                    dictRemoveFile: '<button class="button is-danger"><span>✘</span></button>',
                    init: function () {
                        let previewContainer = document.querySelector(uploadPreview)

                        // cancel pending upload
                        EventHub.listen('clear-pending-upload', this.removeAllFiles(true))

                        // remove
                        this.on('removedfile', debounce((file) => {
                            manager.uploadPreviewOptionsList.some((item, i) => {
                                if (item.name == file.name) {
                                    manager.uploadPreviewOptionsList.splice(i, 1)
                                }
                            })

                            if (!this.files.length) {
                                manager.clearUploadPreview(previewContainer)
                            }

                            manager.uploadPreviewList = this.files
                        }, 100))

                        // add
                        this.on('addedfile', (file) => {
                            let fileList = this.files

                            // remove duplicate files from selection
                            // https://stackoverflow.com/a/32890783/3574919
                            if (fileList.length) {
                                let _i = 0
                                let _len = fileList.length - 1 // -1 to exclude current file
                                for (_i; _i < _len; _i++) {
                                    if (fileList[_i] === file) {
                                        this.removeFile(file)
                                    }
                                }
                            }

                            let el = file.previewElement

                            manager.addToPreUploadedList(file)
                            manager.uploadPreviewList = fileList
                            manager.uploadArea = false
                            manager.toolBar = false
                            manager.infoSidebar = false
                            manager.waitingForUpload = true

                            el.classList.add('is-hidden')
                            previewContainer.classList.add('show')

                            // get around https://www.dropzonejs.com/#config-maxThumbnailFilesize
                            if (!file.dataURL) {
                                let img = el.querySelector('img')
                                img.src = './assets/vendor/MediaManager/noPreview.jpg'
                                img.style.height = '120px'
                                img.style.width = '120px'

                                el.dataset.name = file.name
                                el.classList.remove('is-hidden')

                                manager.$nextTick(() => {
                                    el.querySelector('.dz-image').addEventListener('click', manager.changeUploadPreviewFile)
                                })
                            }
                        })

                        // upload preview
                        this.on('thumbnail', (file, dataUrl) => {
                            file.previewElement.classList.remove('is-hidden')
                        })

                        // reset dz
                        manager.$refs['clear-dropzone'].addEventListener('click', () => {
                            this.removeAllFiles()
                            manager.clearUploadPreview(previewContainer)
                        })

                        // start the upload
                        manager.$refs['process-dropzone'].addEventListener('click', () => {
                            // because dz is dump
                            // https://stackoverflow.com/questions/18059128/dropzone-js-uploads-only-two-files-when-autoprocessqueue-set-to-false
                            queueFix = true
                            this.options.autoProcessQueue = true

                            this.processQueue()
                            manager.clearUploadPreview(previewContainer)
                        })
                    }
                }
                : {
                    init: function () {
                        this.on('addedfile', (file) => {
                            manager.addToPreUploadedList(file)
                        })
                    }
                }

            let options = {
                url: manager.routes.upload,
                parallelUploads: 10,
                hiddenInputContainer: '#new-upload',
                uploadMultiple: true,
                forceFallback: false,
                acceptedFiles: uploadTypes,
                maxFilesize: uploadSize,
                headers: {
                    'X-Socket-Id': manager.browserSupport('Echo') ? Echo.socketId() : null,
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                timeout: 3600000, // 60 mins
                autoProcessQueue: true,
                previewsContainer: `${uploadPreview} .sidebar`,
                accept: function (file, done) {
                    if (this.getUploadingFiles().length) {
                        return done(manager.trans('upload_in_progress'))
                    }

                    if (manager.checkPreUploadedList(file)) {
                        return done(manager.trans('already_exists'))
                    }

                    allFiles++
                    done()
                },
                sending: function (file, xhr, formData) {
                    uploadProgress += parseFloat(100 / allFiles)
                    manager.progressCounter = `${Math.round(uploadProgress)}%`

                    // send files custom options
                    formData.append('custom_attrs', JSON.stringify(manager.uploadPreviewOptionsList))
                },
                processingmultiple() {
                    manager.showProgress = true
                },
                successmultiple(files, res) {
                    res.map((item) => {
                        uploaded++

                        if (item.success) {
                            last = item.file_name
                            let msg = manager.restrictModeIsOn
                                ? `"${item.file_name}"`
                                : `"${item.file_name}" at "${manager.files.path}"`

                            manager.showNotif(`${manager.trans('upload_success')} ${msg}`)
                        } else {
                            manager.showNotif(item.message, 'danger')
                        }
                    })
                },
                errormultiple: function (file, res) {
                    file = Array.isArray(file) ? file[0] : file
                    manager.showNotif(`"${file.name}" ${res}`, 'danger')
                    this.removeFile(file)
                },
                queuecomplete: function () {
                    if (uploaded == this.files.length) {
                        manager.progressCounter = '100%'
                        manager.hideProgress()

                        // reset dz
                        if (queueFix) this.options.autoProcessQueue = false
                        this.removeAllFiles()
                        uploaded = 0
                        allFiles = 0

                        last
                            ? manager.getFiles(null, last)
                            : manager.getFiles()
                    }
                }
            }

            options = Object.assign(options, autoProcess)

            // upload panel
            new Dropzone('#new-upload', options)
            // drag & drop on empty area
            new Dropzone('.__stack-container', Object.assign(options, {clickable: false}))
        },

        clearUploadPreview(previewContainer) {
            previewContainer.classList.remove('show')

            this.$nextTick(() => {
                this.waitingForUpload = false
                this.toolBar = true
                this.smallScreenHelper()
                this.resetInput([
                    'uploadPreviewList',
                    'uploadPreviewNamesList',
                    'uploadPreviewOptionsList'
                ], [])
                this.resetInput('selectedUploadPreviewName')
            })
        },

        // already uploaded checks
        checkPreUploadedList(file) {
            return this.uploadPreviewNamesList.some((name) => name == file.name)
        },
        addToPreUploadedList(file) {
            this.filesNamesList.some((name) => {
                if (name == file.name && !this.checkPreUploadedList(file)) {
                    this.uploadPreviewNamesList.push(name)
                }
            })
        },
        checkForUploadedFile(name) {
            return this.uploadPreviewList.some((file) => file.name == name)
        },

        // show large preview
        changeUploadPreviewFile(e) {
            e.stopPropagation()

            let box = e.target
            let container = box.closest('.dz-preview')

            if (container) {
                let name = container.dataset.name

                if (this.checkForUploadedFile(name)) {
                    this.selectedUploadPreviewName = name

                    // illuminate selected preview
                    this.$nextTick(() => {
                        let active = document.querySelector('.is-previewing')

                        if (active) active.classList.remove('is-previewing')
                        box.classList.add('is-previewing')
                    })
                }

            }
        },

        // upload image from link
        saveLinkForm(event) {
            let url = this.urlToUpload

            if (!url) {
                return this.showNotif(this.trans('no_val'), 'warning')
            }

            this.uploadArea = false
            this.toggleLoading()
            this.loadingFiles('show')

            this.$nextTick(() => {
                axios.post(event.target.action, {
                    path: this.files.path,
                    url: url,
                    random_names: this.useRandomNamesForUpload
                }).then(({data}) => {
                    this.toggleLoading()
                    this.loadingFiles('hide')

                    if (!data.success) {
                        return this.showNotif(data.message, 'danger')
                    }

                    this.resetInput('urlToUpload')
                    this.$nextTick(() => this.$refs.save_link_modal_input.focus())
                    this.showNotif(`${this.trans('save_success')} "${data.message}"`)
                    this.getFiles(null, data.message)

                }).catch((err) => {
                    console.error(err)

                    this.toggleLoading()
                    this.toggleModal()
                    this.loadingFiles('hide')
                    this.ajaxError()
                })
            })
        }
    }
}
