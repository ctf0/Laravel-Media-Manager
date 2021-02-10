<template>
    <div class="wrapper">
        <div data-img-container
             @scroll="updateScrollDir">
            <slot/>
        </div>

        <div class="goo">
            <div v-if="showOps"
                 class="circular-menu"
                 :class="{'active' : opsMenu}">
                <div class="floating-btn"
                     @click="toggleOpsMenu()">
                    <span class="icon is-large"><icon name="cog"/></span>
                </div>

                <menu class="items-wrapper">
                    <!-- move -->
                    <div class="menu-item">
                        <button class="button btn-plain"
                                :disabled="ops_btn_disable"
                                @click.stop="addToMovableList()">
                            <span class="icon is-large">
                                <icon v-if="inMovableList()"
                                      name="shopping-cart"
                                      scale="1.2"/>
                                <icon v-else
                                      name="cart-plus"
                                      scale="1.2"/>
                            </span>
                        </button>
                    </div>

                    <!-- rename -->
                    <div class="menu-item">
                        <button class="button btn-plain"
                                :disabled="ops_btn_disable"
                                @click.stop="renameItem()">
                            <span class="icon is-large">
                                <icon name="terminal"
                                      scale="1.2"/>
                            </span>
                        </button>
                    </div>

                    <!-- editor -->
                    <div class="menu-item">
                        <button class="button btn-plain"
                                :disabled="ops_btn_disable"
                                @click.stop="imageEditorCard()">
                            <span class="icon is-large">
                                <icon name="regular/object-ungroup"
                                      scale="1.2"/>
                            </span>
                        </button>
                    </div>

                    <!-- delete -->
                    <div class="menu-item">
                        <button class="button btn-plain"
                                :disabled="ops_btn_disable"
                                @click.stop="deleteItem()">
                            <span class="icon is-large">
                                <icon name="regular/trash-alt"
                                      scale="1.2"/>
                            </span>
                        </button>
                    </div>
                </menu>
            </div>
        </div>

        <transition :name="scrollBtn.state ? 'mm-img-nxt' : 'mm-img-prv'"
                    appear>
            <div v-show="scrollBtn.state"
                 class="scroll-btn"
                 @click.self.stop="scrollImg">
                <span class="icon is-large">
                    <icon name="chevron-down"
                          :class="scrollBtn.dir"/>
                </span>
            </div>
        </transition>
    </div>
</template>

<script>
import debounce        from 'lodash/debounce'
import animateScrollTo from '../../packages/animated-scroll-to'

export default {
    props: [
        'trans',
        'showOps',
        'ops_btn_disable',
        'inMovableList',
        'renameItem',
        'deleteItem',
        'imageEditorCard',
        'addToMovableList'
    ],
    data() {
        return {
            scrollBtn: {
                state : false,
                dir   : 'down'
            },
            opsMenu: false
        }
    },
    created() {
        window.addEventListener('resize', this.isScrollable)
    },
    mounted() {
        this.isScrollable()
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.isScrollable)
    },
    methods: {
        isScrollable() {
            let item = this.getContainer(this.$el)

            if (item) {
                return this.scrollBtn.state = item.scrollHeight > item.offsetHeight
            }
        },
        updateScrollDir: debounce(function(e) {
            let item   = e.target
            let margin = 3

            return this.scrollBtn.dir = (item.scrollTop + item.clientHeight) >= (item.scrollHeight - margin)
                ? 'up'
                : 'down'
        }, 250),
        scrollImg(e) {
            let item = this.getContainer(e.target.parentNode)

            return animateScrollTo(item, {
                speed       : 250,
                maxDuration : 500,
                offset      : this.scrollBtn.dir == 'up' ? -item.scrollHeight : item.scrollHeight,
                element     : item,
                useKeys     : true
            })
        },
        getContainer(el) {
            return el.querySelector('[data-img-container]')
        },
        toggleOpsMenu() {
            return this.opsMenu = !this.opsMenu
        }
    }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/modules/scroll-btn';
@import '../../../sass/packages/goo';

.wrapper {
    overflow: hidden;
    position: relative;

    > div:first-child {
        max-height: 40vh;
        overflow-y: scroll;
    }
}

</style>
