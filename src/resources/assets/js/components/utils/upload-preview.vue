<template>
    <section class="preview-container"
             style="--width: 25%;">
        <!-- preview -->
        <focus-point v-if="img"
                     v-model="options.focalPoint">
            <img :src="img">
        </focus-point>

        <div v-else
             class="icons-preview">
            <icon-types :file="type"
                        :file-type-is="fileTypeIs"
                        :scale="10"
                        :except="['image']"/>
        </div>

        <!-- options -->
        <div v-if="img"
             class="options btn-animate"
             :class="{'show': panelIsVisible}">
            <button v-tippy="{arrow: true, placement: 'left'}"
                    class="btn-plain"
                    :class="{'alt': panelIsVisible}"
                    :title="trans('options')"
                    @click.stop="switchPanel()">
                <span class="icon is-large">
                    <icon>
                        <icon name="circle"
                              scale="2.5"/>
                        <icon name="cog"
                              class="icon-btn"/>
                    </icon>
                </span>
            </button>

            <!-- panel -->
            <div :class="{'show': panelIsVisible}"
                 class="panel">
                <!-- dims -->
                <section class="dimensions">
                    <h3 class="is-size-4">
                        {{ trans('dimension') }}:
                    </h3>
                    <div class="data-container">
                        <div class="field has-addons">
                            <div class="control">
                                <a class="button is-link no-click">
                                    W
                                </a>
                            </div>
                            <div class="control full-width">
                                <input v-model.number="options.dimensions.w"
                                       class="input">
                            </div>
                        </div>
                        <div class="field has-addons">
                            <div class="control">
                                <a class="button is-link no-click">
                                    H
                                </a>
                            </div>
                            <div class="control full-width">
                                <input v-model.number="options.dimensions.h"
                                       class="input">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- focal -->
                <section class="focals">
                    <h3 class="is-size-4">
                        {{ trans('focals') }}:
                    </h3>
                    <div class="data-container">
                        <div class="field has-addons">
                            <div class="control">
                                <a class="button is-link no-click">
                                    X
                                </a>
                            </div>
                            <div class="control full-width">
                                <input v-model.number="options.focalPoint.x"
                                       class="input">
                            </div>
                        </div>

                        <div class="field has-addons">
                            <div class="control">
                                <a class="button is-link no-click">
                                    Y
                                </a>
                            </div>
                            <div class="control full-width">
                                <input v-model.number="options.focalPoint.y"
                                       class="input">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- extra data -->
                <section class="extras">
                    <div class="level">
                        <h3 class="is-size-4">
                            Extra Data:
                        </h3>
                        <!-- add more -->
                        <button class="button is-success"
                                @click.stop="addToExtra()">
                            <span class="icon is-small">
                                <icon name="plus"
                                      scale="1.2"/>
                            </span>
                        </button>
                    </div>

                    <!-- items -->
                    <section class="arr">
                        <div v-for="(item,i) in options.extra"
                             :key="i"
                             class="data-container">
                            <div class="field has-addons">
                                <!-- key -->
                                <div class="control full-width">
                                    <input v-model="item.name"
                                           placeholder="key"
                                           class="input">
                                </div>
                                <!-- val -->
                                <div class="control full-width">
                                    <input v-model="item.data"
                                           placeholder="value"
                                           class="input">
                                </div>
                                <!-- remove -->
                                <p class="control">
                                    <a class="button is-black"
                                       @click.stop="removeFromExtra(i)">
                                        <span class="icon is-small">
                                            <icon name="times"/>
                                        </span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </section>
                </section>

                <!-- desc -->
                <section>
                    <h3 class="is-size-4">
                        {{ trans('description') }}:
                    </h3>
                    <textarea v-model="options.description"
                              rows="10"
                              class="textarea"/>
                </section>
            </div>
        </div>

        <!-- info -->
        <div v-show="!panelIsVisible"
             class="info">
            {{ name }}
        </div>
    </section>
</template>

<style lang="scss">
    @import '../../../sass/partials/vars';
    @import '../../../sass/packages/focal-point';

    img {
        display: block;
    }

    .preview-container {
        height: 100%;
        overflow-x: hidden;
        overflow-y: scroll;
        position: relative;
        width: 100%;

        .icons-preview {
            align-items: center;
            display: flex;
            height: 100%;
            justify-content: center;
            width: 100%;
        }

        .info {
            background: linear-gradient(45deg, $black, transparent);
            bottom: 0;
            color: $white;
            left: -2px;
            padding: 0.5rem 1rem;
            position: sticky;
            transition: all $anim-time ease;

            &:hover {
                opacity: 0;
            }

            &:empty {
                padding: 0;
            }
        }

        .options {
            align-items: flex-start;
            display: flex;
            height: 100%;
            position: fixed;
            top: 0;
            transition: all $anim-time ease;
            width: var(--width);

            &.show {
                right: 0 !important;
                z-index: 3;
            }

            .btn-plain {
                padding-right: $option_btns-space;
                padding-top: $option_btns-space;
                z-index: 2;

                &.alt {
                    color: $black !important;
                    opacity: 1 !important;

                    .icon-btn {
                        color: $white !important;
                    }
                }
            }

            .panel {
                backdrop-filter: blur(5px);
                background-color: rgba(darken($active_theme, 5%), 0.8);
                border-radius: 0;
                display: flex;
                flex-direction: column;
                height: 100%;
                overflow: scroll;
                padding: 1rem;
                width: 100%;

                > section:last-child {
                    margin-top: auto;
                }
            }

            .dimensions,
            .focals,
            .extras {
                margin-bottom: 1rem;

                .data-container {
                    display: flex;
                    width: 100%;
                }

                .field {
                    width: 50%;

                    &:first-of-type {
                        margin-right: 0.75rem;
                    }
                }
            }

            .extras {
                .level {
                    margin: 0;
                }

                .data-container {
                    margin-bottom: 0.5rem;

                    &:last-of-type {
                        margin: 0;
                    }
                }

                .field {
                    margin: 0 !important;
                    width: 100%;
                }

                .arr {
                    margin-top: 1rem;

                    &:empty {
                        margin-bottom: 0;
                    }
                }
            }

            textarea,
            input {
                box-shadow: none;
                opacity: 0.5;
                transition: all $anim-time ease;

                &:focus {
                    opacity: 0.8;
                }
            }

            h3 {
                margin-bottom: 0.25rem;
            }
        }
    }

    @include media('max', 1023) {
        .preview-container {
            .info {
                left: 0;
            }

            .options {
                display: none;
            }
        }
    }

</style>

<script>
import cloneDeep from 'lodash/cloneDeep'
import FocusPoint from 'vue-focuspoint-component'

export default {
    components: {
        FocusPoint
    },
    props: [
        'file',
        'fileTypeIs',
        'trans'
    ],
    data() {
        return {
            img: this.file.dataURL || null,
            type: this.file.type,
            name: this.file.name,
            panelIsVisible: false,

            options: {
                focalPoint: {
                    x: 50,
                    y: 50
                },
                dimensions: {
                    w: this.file.width || 0,
                    h: this.file.height || 0
                },
                description: null,
                extra: []
            }
        }
    },
    activated() {
        this.updateParentPanel()
        this.addSpaceToOptBtn()
    },
    methods: {
        updateParentPanel() {
            this.$parent.uploadPreviewOptionsPanelIsVisible = this.panelIsVisible
        },
        addSpaceToOptBtn() {
            let cont = document.querySelector('.options')

            if (cont) {
                let btn = cont.querySelector('.btn-plain')
                cont.style.right = `calc((var(--width) * -1) + ${btn.offsetWidth}px)`
            }
        },
        switchPanel() {
            return this.panelIsVisible = !this.panelIsVisible
        },
        addToExtra() {
            this.options.extra.push({
                name: null,
                data: null
            })
        },
        removeFromExtra(i) {
            this.options.extra.splice(i, 1)
        }
    },
    watch: {
        options: {
            deep: true,
            handler(val) {
                let list = this.$parent.uploadPreviewOptionsList
                let data = cloneDeep(val)
                data.extra = data.extra.filter((item) => item.name || item.data) || []

                let index = list.findIndex((e) => e.name == this.name)
                index < 0
                    ? list.push({
                        name: this.name,
                        options: data
                    })
                    : list[index].options = data
            }
        },
        panelIsVisible(val) {
            this.updateParentPanel()
        }
    }
}
</script>
