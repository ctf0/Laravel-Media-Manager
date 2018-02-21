{{-- manager --}}
<div class="modal mm-animated fadeIn is-active modal-manager__Inmodal">
    <div class="modal-background" @click="hideInputModal()"></div>
    <div class="modal-content mm-animated fadeInDown">
        <div class="box">
            @include('MediaManager::_manager', ['modal' => true])
        </div>
    </div>
    <button class="modal-close is-large is-hidden-touch" @click="hideInputModal()"></button>
</div>
