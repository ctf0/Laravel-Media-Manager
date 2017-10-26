{{-- manager --}}
<div class="modal mm-animated fadeIn is-active" v-if="showModal">
    <div class="modal-background" @click="toggleModal()"></div>
    <div class="modal-content mm-animated fadeInDown" style="width: 70%;">
        <div class="box">
            @include('MediaManager::_manager')
        </div>
    </div>
    <button class="modal-close is-large" @click="toggleModal()"></button>
</div>
