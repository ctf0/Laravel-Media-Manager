$.ajaxSetup({
	cache: false,
	headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	}
});

var manager = new Vue({
	el: '#app',
	data: {
		files: [],
		folders: [],
		directories: [],
		filterdList: [],
		bulkList: [],
		currentFilterName: null,
		selectedFile: null,
		searchItemsCount: null,
		searchFor: null,
	},
	methods: {
		/*                Main                */
		getFiles(folders) {
			$('#file_loader').show()
			this.searchFor = ''
			this.showFilesOfType('all')

			if (folders != '/') {
				var folder_location = '/' + folders.join('/')
			} else {
				var folder_location = '/'
			}

			// files list
			$.post(`${media_root_url}/files`, {
				folder: folder_location,
			}, (res) => {
				this.files = res
				$('#file_loader').hide()
				this.selectFirst()
				$('#right').fadeIn()

				for (var i = this.allItemsCount - 1; i >= 0; i--) {
					if (typeof this.allFiles[i].size !== 'undefined') {
						this.allFiles[i].size = this.bytesToSize(this.allFiles[i].size)
					}
				}
			})

			// dirs list
			this.updateDirsList()
		},
		bytesToSize(bytes) {
			var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
			if (bytes === 0) return '0 Bytes'
			var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
			return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i]
		},
		confirm_delete(files) {
			$.post(`${media_root_url}/delete_file_folder`, {
				folder_location: this.folders,
				deleted_files: files
			}, (res) => {
				res.data.map((item) => {
					if (item.success) {
						EventHub.fire('showNotif', {
							title: 'Success',
							body: `Successfully Deleted "${item.name}"`,
							type: 'warning',
							duration: 5
						})
						this.removeFromLists(item.name)
					} else {
						EventHub.fire('showNotif', {
							title: 'Error',
							body: item.message,
							type: 'danger',
						})
					}
				})

				$('#confirm_delete_modal').modal('hide')
				this.selectFirst()
			})
		},
		move_btn(files) {
			destination = $('#move_folder_dropdown').val()

			$.post(`${media_root_url}/move_file`, {
				folder_location: this.folders,
				destination: destination,
				moved_files: files
			}, (res) => {
				res.data.map((item) => {
					if (item.success) {
						EventHub.fire('showNotif', {
							title: 'Success',
							body: `Successfully moved "${item.name}" to "${destination}"`,
							type: 'success',
							duration: 5
						})
						this.removeFromLists(item.name)
						this.updateFolderCount(destination)

						// update dirs list after move
						if (item.type.includes('folder')) {
							this.updateDirsList()
						}
					} else {
						EventHub.fire('showNotif', {
							title: 'Error',
							body: item.message,
							type: 'danger',
						})
					}
				})

				$('#move_file_modal').modal('hide')
				this.selectFirst()
			})
		},

		/*                Bulk                */
		isBulkSelecting() {
			return $('#blk_slct').hasClass(errorClass)
		},
		IsInBulkList(file) {
			return this.bulkList.includes(file)
		},
		pushtoBulkList(file) {
			if (!this.bulkItemsCount) {
				return this.bulkList.push(file)
			}

			if (!this.bulkList.includes(file)) {
				return this.bulkList.push(file)
			} else {
				this.bulkList.splice(this.bulkList.indexOf(file), 1)

				// normal single selction behavior
				if (!$('#blk_slct_all').hasClass(warningClass)) {
					// select prev item
					if (this.bulkItemsCount) {
						this.selectedFile = this.bulkList[this.bulkItemsCount - 1]
					} else {
						// clear slection
						this.clearSelected()
					}
				}
			}
		},

		/*                Selected                */
		selectFirst() {
			this.$nextTick(() => {
				file = $('div[data-index="0"]')
				if (file.length > 0) {
					file.trigger('click')
				}
			})
		},
		setSelected(file) {
			this.clearSelected()
			$('div[data-folder="' + file.name + '"]').addClass('selected')
			this.selectedFile = file

			if (this.isBulkSelecting()) {
				this.pushtoBulkList(file)
			}
		},
		clearSelected() {
			this.selectedFile = undefined
			$('#files li .selected').removeClass('selected')
		},
		openFolder(file) {
			if (!this.isBulkSelecting()) {
				if (!this.fileTypeIs(file, 'folder')) {
					return false
				}
				this.folders.push(file.name)
				this.getFiles(this.folders)
			}
			manager.currentFilterName = null
		},
		goToFolder(index) {
			if (!this.isBulkSelecting()) {
				this.folders = this.folders.splice(0, index)
				this.getFiles(this.folders)
			}
		},
		scrollToFile(file) {
			if (!file) {
				file = $('div[data-index="0"]')
			}

			$(file).trigger('click')
			$('#left').scrollTo($(file), 0, {
				margin: true,
				offset: -8
			});
		},

		/*                Filtration                */
		btnFilter(val) {
			if (val == 'all') {
				return this.filterdList.length
			}

			return this.files.items.some((item) => {
				return this.fileTypeIs(item, val)
			})
		},
		selectedFileIs(val) {
			if (typeof this.selectedFile !== 'undefined') {
				return this.fileTypeIs(this.selectedFile, val)
			}
		},
		filterNameIs(val) {
			return this.currentFilterName == val
		},
		fileTypeIs(item, val) {
			if (val == 'text') {
				if (!item.type.includes('folder') &&
					!item.type.includes('image') &&
					!item.type.includes('video') &&
					!item.type.includes('audio')) {
					return true
				}
			} else {
				return item.type.includes(val)
			}
		},
		showFilesOfType(val) {
			if (this.currentFilterName == val) {
				return false
			}
			if (val == 'all') {
				this.filterdList = []
			} else {
				this.filterdList = this.files.items.filter((item) => {
					return this.fileTypeIs(item, val)
				})
			}

			if (!this.isBulkSelecting()) {
				this.clearSelected()
				this.selectFirst()
			}
			if (this.searchFor) {
				this.updateSearchCount()
			}

			this.currentFilterName = val
		},
		filterDir(dir) {
			// dont show dirs that have similarity with selected item(s)
			if (this.bulkItemsCount) {
				if (this.bulkList.filter(e => dir.match(`(\/?)${e.name}(\/?)`)).length > 0) {
					return false;
				} else {
					return true;
				}
			} else {
				return this.selectedFile && !dir.includes(this.selectedFile.name)
			}
		},
		checkForFolders() {
			if ($('#move_folder_dropdown').val() !== null) {
				return true
			} else {
				return false
			}
		},

		/*                Operations                */
		removeFromLists(name) {
			if (this.filterdList.length) {
				for (var i = this.filterdList.length - 1; i >= 0; i--) {
					if (this.filterdList[i].name.includes(name)) {
						this.filterdList.splice(i, 1)
						break
					}
				}
			}

			if (this.directories.length) {
				for (var i = this.directories.length - 1; i >= 0; i--) {
					if (this.directories[i].includes(name)) {
						this.directories.splice(i, 1)
					}
				}
			}

			for (var i = this.files.items.length - 1; i >= 0; i--) {
				if (this.files.items[i].name.includes(name)) {
					this.files.items.splice(i, 1)
					break
				}
			}

			this.clearSelected()
		},
		updateFolderCount(destination) {
			if (destination !== '../') {

				if (destination.includes('/')) {
					destination = destination.split("/").shift()
				}

				if (this.filterdList.length) {
					for (var i = this.filterdList.length - 1; i >= 0; i--) {
						if (this.filterdList[i].name.includes(destination)) {
							this.filterdList[i].items += 1
							break
						}
					}
				}

				for (var i = this.files.items.length - 1; i >= 0; i--) {
					if (this.files.items[i].name.includes(destination)) {
						this.files.items[i].items += 1
						break
					}
				}
			}
		},
		updateItemName(item, oldName, newName) {
			// update the main files list
			var filesIndex = this.files.items[this.files.items.indexOf(item)]
			filesIndex.name = newName
			filesIndex.path = filesIndex.path.replace(oldName, newName)

			// if found in the filterd list, then update it aswell
			if (this.filterdList.includes(item)) {
				var filterIndex = this.filterdList[this.filterdList.indexOf(item)]
				filterIndex.name = newName
				filesIndex.path = filterIndex.path.replace(oldName, newName)
			}
		},
		updateSearchCount() {
			this.$nextTick(() => {
				this.searchItemsCount = parseInt($('#files li').length)

				if (this.searchItemsCount == 0) {
					$('#no_files').fadeIn()
				} else {
					$('#no_files').hide()
				}
			})
		},
		updateDirsList() {
			$.post(`${media_root_url}/directories`, {
				folder_location: manager.folders,
			}, function(data) {
				manager.directories = data
			})
		},

		/*                Utils                */
		lastItem(item, list) {
			return item == list[list.length - 1]
		},
		toggleInfo() {
			$('#right').fadeToggle()
			var span = $('.toggle').find('span').not('.icon')
			span.text(span.text() == "Close" ? "Open" : "Close")
			$('.toggle').find('.fa').toggleClass('fa fa-angle-double-right').toggleClass('fa fa-angle-double-left')
		},
		lightBoxIsActive() {
			return $('#vue-lightboxOverlay').is(':visible')
		}
	},

	computed: {
		allFiles() {
			if (typeof this.filterdList !== 'undefined' && this.filterdList.length > 0) {
				return this.filterdList
			} else {
				return this.files.items
			}
		},
		allItemsCount() {
			if (typeof this.allFiles !== 'undefined' && this.allFiles.length > 0) {
				return this.allFiles.length
			}
		},
		bulkItemsCount() {
			if (typeof this.bulkList !== 'undefined' && this.bulkList.length > 0) {
				return this.bulkList.length
			}
		},
	},

	watch: {
		allFiles(newVal, oldVal) {
			if (newVal.length < 1) {
				$('#no_files').fadeIn()
			} else {
				$('#no_files').hide()
			}
		},
		bulkList(val) {
			if (val) {
				// hide move button when all folders are selected
				this.$nextTick(() => {
					if (!this.checkForFolders()) {
						$('#move').attr('disabled', true)
					}
				})
			}

			if (val == 0 && this.isBulkSelecting()) {
				var toggle_text = $('#blk_slct_all').find('span').not('.icon')
				$('#blk_slct_all').removeClass(warningClass)
				$('#blk_slct_all').find('.fa').removeClass('fa-minus').addClass('fa-plus')
				toggle_text.text("Select All")
			}
		},
		selectedFile(val) {
			if (!val) {
				$('#move').attr('disabled', true)
				$('#rename').attr('disabled', true)
				$('#delete').attr('disabled', true)
			} else {
				// hide move button when there is only one folder and its selected
				this.$nextTick(() => {
					if (!this.checkForFolders()) {
						$('#move').attr('disabled', true)
					}
				})

				$('#move').removeAttr("disabled")
				$('#rename').removeAttr("disabled")
				$('#delete').removeAttr("disabled")
			}
		},
		searchFor(val) {
			if (val) {
				this.updateSearchCount()
			}

			// so we dont miss with the bulk selection list
			if (!this.isBulkSelecting()) {
				this.clearSelected()
				this.selectFirst()
			}
			this.searchItemsCount = null
		},
		searchItemsCount(val) {
			// make sure "no_files" is hidden when search query is cleared
			if (val == null) {
				$('#no_files').hide()
			}
		}
	}
})

$(function() {

	manager.getFiles('/')

	//********** File Upload **********//

	$("#new-upload").dropzone({
		createImageThumbnails: false,
		parallelUploads: 10,
		uploadMultiple: true,
		previewsContainer: '#uploadPreview',
		processingmultiple() {
			$('#uploadProgress').fadeIn()
		},
		totaluploadprogress(uploadProgress, totalBytes, totalBytesSent) {
			$('#uploadProgress .progress-bar').css('width', uploadProgress + '%')
		},
		successmultiple(files, res) {
			res.data.map(function(item) {
				if (item.success) {
					EventHub.fire('showNotif', {
						title: 'Success',
						body: `Successfully Uploaded "${item.message}"`,
						type: 'success',
						duration: 5
					})
				} else {
					EventHub.fire('showNotif', {
						title: 'Error',
						body: item.message,
						type: 'danger',
					})
				}
			})
			manager.getFiles(manager.folders)
		},
		errormultiple(files, res, xhr) {
			EventHub.fire('showNotif', {
				title: 'Error',
				body: res,
				type: 'danger',
			})
		},
		queuecomplete() {
			$('#upload').trigger('click')
			$('#uploadProgress').fadeOut(function() {
				$('#uploadProgress .progress-bar').css('width', 0)
			})
		}
	})

	//********** Key Press **********//

	$(document).keydown(function(e) {

		var curSelected = parseInt($('#files li .selected').data('index'))

		// when modal isnt visible
		if (!$('#new_folder_modal').is(':visible') &&
			!$('#move_file_modal').is(':visible') &&
			!$('#rename_file_modal').is(':visible') &&
			!$('#confirm_delete_modal').is(':visible')) {

			// when search is not focused
			if (!$('.input').is(":focus")) {

				// when no bulk selecting & no light box is active
				if (!manager.isBulkSelecting() && !manager.lightBoxIsActive()) {
					if ((keycode(e) == 'left' || keycode(e) == 'up') && curSelected !== 0) {
						newSelected = curSelected - 1
						cur = $('div[data-index="' + newSelected + '"]')
						manager.scrollToFile(cur)
					}

					if ((keycode(e) == 'right' || keycode(e) == 'down') && curSelected < manager.allItemsCount - 1) {
						newSelected = curSelected + 1
						cur = $('div[data-index="' + newSelected + '"]')
						manager.scrollToFile(cur)
					}

					// open folder
					if (keycode(e) == 'enter') {
						if (!manager.selectedFileIs('folder')) {
							return false
						}
						manager.currentFilterName = null
						manager.folders.push(manager.selectedFile.name)
						manager.getFiles(manager.folders)
					}

					// go up a dir
					if (keycode(e) == 'backspace') {
						index = parseInt(manager.folders.length) - 1
						if (index < 0) {
							return false
						}
						if (index === 0) {
							manager.folders = []
							manager.getFiles(manager.folders)
						} else {
							manager.folders = manager.folders.splice(0, index)
							manager.getFiles(manager.folders)
						}
						manager.currentFilterName = null
					}

					// go to first / last item
					if (manager.allItemsCount) {
						if (keycode(e) == 'home') {
							manager.scrollToFile()
						}
						if (keycode(e) == 'end') {
							index = manager.allItemsCount - 1
							cur = $('div[data-index="' + index + '"]')
							manager.scrollToFile(cur)
						}
					}

					// file upload
					if (keycode(e) == 'u') {
						$('#upload').trigger('click')
					}
				}
				/* end of no bulk selection */

				// when there are files
				if (manager.allItemsCount) {

					// when lightbox is not active
					if (!manager.lightBoxIsActive()) {
						// bulk select
						if (keycode(e) == 'b') {
							$('#blk_slct').trigger('click')
						}

						// add all to bulk list
						if (manager.isBulkSelecting() && keycode(e) == 'a') {
							$('#blk_slct_all').trigger('click')
						}

						// delete file
						if (keycode(e) == 'delete' || keycode(e) == 'd') {
							$('#delete').trigger('click')
						}

						// refresh
						if (keycode(e) == 'r') {
							$('#refresh').trigger('click')
						}

						// move file
						if (manager.checkForFolders()) {
							if (keycode(e) == 'm') {
								$('#move').trigger('click')
							}
						}
					}
					/* end when lightbox is not active */

					if (keycode(e) == 'space' && e.target == document.body) {
						// prevent body from scrolling
						e.preventDefault();

						// play audio/video
						if (manager.selectedFileIs('video') || manager.selectedFileIs('audio')) {
							return $('.player')[0].paused ? $('.player')[0].play() : $('.player')[0].pause()
						}

						// quick view image
						if (manager.selectedFileIs('image')) {
							if (manager.lightBoxIsActive()) {
								$('#vue-lightboxOverlay').trigger('click')
							} else {
								$('.quickView').trigger('click')
							}
						}
					}

					// quick view image "esc"
					if (keycode(e) == 'esc' && manager.selectedFileIs('image') && manager.lightBoxIsActive()) {
						$('#vue-lightboxOverlay').trigger('click')
						e.preventDefault();
					}
				}
				/* end of there are files */

				// toggle file details box
				if (keycode(e) == 't' && !manager.lightBoxIsActive()) {
					$('.toggle').trigger('click')
				}
			}
			/* end of search is not focused */
		}
		/* end of modal isnt visible */

		// when modal is visible
		if (keycode(e) == 'enter') {
			if ($('#confirm_delete_modal').is(':visible')) {
				$('#confirm_delete').trigger('click')
			}

			if ($('#rename_file_modal').is(':visible')) {
				$('#rename_btn').trigger('click')
			}

			if ($('#new_folder_modal').is(':visible')) {
				$('#new_folder_submit').trigger('click')
			}
		}
		/* end of modal is visible */
	})

	//********** Toolbar Buttons **********//

	// bulk select
	$('#blk_slct').click(function() {
		$(this).toggleClass(errorClass)
		$('#upload, #new_folder, #refresh, #rename').parent().hide()
		$(this).closest('.field').toggleClass('has-addons')
		$('#blk_slct_all').fadeIn()

		// reset when toggled off
		if (!manager.isBulkSelecting()) {
			$('#upload, #new_folder, #refresh, #rename').parent().show()
			if ($('#blk_slct_all').hasClass(warningClass)) {
				$('#blk_slct_all').trigger('click')
			}
			$('#blk_slct_all').hide()

			$('li.bulk-selected').removeClass('bulk-selected')
			manager.bulkList = []
			manager.selectFirst()
		}

		manager.clearSelected()
	})

	// select all files
	$('#blk_slct_all').click(function() {
		// if no items in bulk list
		if (manager.bulkList == 0) {
			// if no search query
			if (!manager.searchFor) {
				$(this).addClass(warningClass)
				manager.bulkList = manager.allFiles.slice(0)
			}

			// if found search items
			if (manager.searchItemsCount) {
				$(this).addClass(warningClass)
				$('#files li').each(function() {
					$(this).trigger('click')
				})
			}
		}

		// if having search + having bulk items < search found items
		else if (manager.searchFor && (manager.bulkItemsCount < manager.searchItemsCount)) {
			manager.bulkList = []
			manager.clearSelected()

			if ($(this).hasClass(warningClass)) {
				$(this).removeClass(warningClass)
			} else {
				$(this).addClass(warningClass)
				$('#files li').each(function() {
					$(this).trigger('click')
				})
			}
		}

		// if NO search + having bulk items < all items
		else if (!manager.searchFor && (manager.bulkItemsCount < manager.allItemsCount)) {
			if ($(this).hasClass(warningClass)) {
				$(this).removeClass(warningClass)
				manager.bulkList = []
			} else {
				$(this).addClass(warningClass)
				manager.bulkList = manager.allFiles.slice(0)
			}

			manager.clearSelected()
		}

		// otherwise
		else {
			$(this).removeClass(warningClass)
			manager.bulkList = []
			manager.clearSelected()
		}

		// if we have items in bulk list, select first item
		if (manager.bulkItemsCount) {
			manager.selectedFile = manager.bulkList[0]
		}

		// toggle styling
		var toggle_text = $(this).find('span').not('.icon')
		if ($(this).hasClass(warningClass)) {
			$(this).find('.fa').removeClass('fa-plus').addClass('fa-minus')
			toggle_text.text("Select Non")
		} else {
			$(this).find('.fa').removeClass('fa-minus').addClass('fa-plus')
			toggle_text.text("Select All")
		}
	})

	// refresh
	$('#refresh').click(function() {
		manager.getFiles(manager.folders)
	})

	// upload
	$('#upload').click(function() {
		$('#new-upload').fadeToggle('fast')
	})

	// new folder
	$('#new_folder').click(function() {
		$('#new_folder_modal').modal('show')
	})

	$('#new_folder_modal').on('shown.bs.modal', function() {
		$("#new_folder_name").focus()
	})

	$('#new_folder_submit').click(function() {
		$.post(`${media_root_url}/new_folder`, {
			current_path: manager.files.path,
			new_folder_name: $('#new_folder_name').val(),
		}, function(data) {
			if (data.success) {
				EventHub.fire('showNotif', {
					title: 'Success',
					body: `Successfully Created "${$('#new_folder_name').val()}" at "${data.new_folder}"`,
					type: 'success',
					duration: 5
				})
				manager.getFiles(manager.folders)
			} else {
				EventHub.fire('showNotif', {
					title: 'Error',
					body: data.message,
					type: 'danger',
				})
			}

			$('#new_folder_name').val('')
			$('#new_folder_modal').modal('hide')
		})
	})

	// delete
	$('#delete').click(function() {
		if (!manager.isBulkSelecting()) {
			if (manager.selectedFileIs('folder')) {
				$('.folder_warning').show()
			} else {
				$('.folder_warning').hide()
			}
			$('.confirm_delete').text(manager.selectedFile.name)
		}

		if (manager.bulkItemsCount) {
			$('.folder_warning').hide()
			manager.bulkList.some((item) => {
				if (item.type.includes('folder')) {
					$('.folder_warning').show()
				}
			})
		}

		$('#confirm_delete_modal').modal('show')
	})

	$('#confirm_delete').click(function() {
		if (manager.bulkItemsCount) {
			manager.confirm_delete(manager.bulkList)
			$('#blk_slct').trigger('click')
		} else {
			manager.confirm_delete([manager.selectedFile])
		}
	})

	// move
	$('#move').click(function() {
		$('#move_file_modal').modal('show')
	})

	$('#move_btn').click(function() {
		if (manager.bulkItemsCount) {
			manager.move_btn(manager.bulkList)
			$('#blk_slct').trigger('click')
		} else {
			manager.move_btn([manager.selectedFile])
		}
	})

	// rename
	$('#rename').click(function() {
		$('#rename_file_modal').modal('show')
	})

	$('#rename_file_modal').on('shown.bs.modal', function() {
		$("#new_filename").focus()
	})

	$('#rename_btn').click(function() {
		source = manager.selectedFile.path
		filename = manager.selectedFile.name
		new_filename = $('#new_filename').val()

		$.post(`${media_root_url}/rename_file`, {
			folder_location: manager.folders,
			filename: filename,
			new_filename: new_filename,
		}, function(data) {
			if (data.success) {
				EventHub.fire('showNotif', {
					title: 'Success',
					body: `Successfully Renamed "${filename}" to "${data.new_filename}"`,
					type: 'success',
					duration: 5
				})
				manager.updateItemName(manager.selectedFile, filename, data.new_filename)
			} else {
				EventHub.fire('showNotif', {
					title: 'Error',
					body: data.message,
					type: 'danger',
				})
			}

			$('#rename_file_modal').modal('hide')
		})
	})
})
