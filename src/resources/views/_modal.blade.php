{{-- manager --}}
<div class="modal mm-animated fadeIn is-active modal-manager__Inmodal" v-if="showModal">
    <div class="modal-background" @click="toggleModal()"></div>
    <div class="modal-content mm-animated fadeInDown">
        <div class="box">
            @include('MediaManager::_manager', ['modal'=>true])
        </div>
    </div>
    <button class="modal-close is-large is-hidden-touch" @click="toggleModal()"></button>
</div>
