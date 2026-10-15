@extends('main')

@section('title', 'My Profile')

@section('content')

<style>
.profile-page .profile-card,
.profile-page .profile-form-card {
    border:1px solid #e8edf4;
    border-radius:10px;
    overflow:hidden;
}
.profile-page .profile-summary {
    background:linear-gradient(115deg,#1d4ed8 0%,#4f46e5 100%);
    color:#fff;
    padding:24px 28px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    gap:18px;
    min-height:280px;
    text-align:center;
}
.profile-page .profile-avatar {
    width:68px;
    height:68px;
    flex:0 0 68px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(255,255,255,.16);
    border:1px solid rgba(255,255,255,.25);
    font-size:34px;
    object-fit:cover;
}
.profile-page .profile-avatar-trigger {
    position:relative;
    padding:0;
    color:#fff;
    cursor:pointer;
}
.profile-page .profile-avatar-trigger:hover .profile-avatar-camera,
.profile-page .profile-avatar-trigger:focus-visible .profile-avatar-camera { opacity:1; }
.profile-page .profile-avatar-photo { width:100%; height:100%; border-radius:inherit; object-fit:cover; }
.profile-page .profile-avatar-camera {
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:inherit;
    background:rgba(15,23,42,.5);
    opacity:0;
    transition:opacity .15s ease;
    font-size:20px;
}
.profile-page .profile-role {
    display:inline-block;
    margin-top:8px;
    padding:4px 10px;
    border-radius:6px;
    background:rgba(255,255,255,.16);
    color:#fff;
    font-size:12px;
    font-weight:600;
}
.profile-page .profile-form-card .card-header {
    padding:16px 20px;
    background:#f8f9fc;
    border-bottom:1px solid #edf0f5;
}
.profile-page .profile-form-card .card-body { padding:22px; }
.profile-page .form-label { color:#374151; font-size:13px; font-weight:600; }
.profile-page .form-control { min-height:44px; border-radius:8px; }
.profile-page .profile-save-btn { min-height:44px; padding:9px 18px; border-radius:8px; font-weight:600; }
.profile-page .profile-image-input { padding:10px 12px; }
.profile-crop-stage {
    position:relative;
    width:min(320px, 100%);
    aspect-ratio:1 / 1;
    overflow:hidden;
    border-radius:8px;
    background:#111827;
    cursor:move;
    touch-action:none;
    user-select:none;
}
.profile-crop-stage img {
    position:absolute;
    max-width:none;
    max-height:none;
    pointer-events:none;
    user-select:none;
}
html[data-bs-theme="dark"] .profile-page .profile-form-card .card-header {
    background:#273449 !important;
    color:#f3f4f6 !important;
}
html[data-bs-theme="dark"] .profile-page .form-label { color:#e5e7eb; }
</style>

<div class="container-fluid py-4 profile-page">
<div class="row g-4">
    <div class="col-xl-4 col-lg-5">

        <div class="card profile-card shadow-sm h-100">
            <div class="profile-summary">
                <button type="button" class="profile-avatar profile-avatar-trigger" id="profile-avatar-trigger" aria-label="Choose profile photo" title="Click to change profile photo">
                    @if($user->profile_image)
                        <img class="profile-avatar-photo" id="profile-avatar-preview" src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }} profile photo">
                    @else
                        <i class="bi bi-person-fill" id="profile-avatar-placeholder"></i>
                        <img class="profile-avatar-photo d-none" id="profile-avatar-preview" alt="Profile photo preview">
                    @endif
                    <span class="profile-avatar-camera"><i class="bi bi-camera-fill"></i></span>
                </button>
                <div class="min-w-0">
                    <h4 class="mb-1 fw-bold">{{ $user->name }}</h4>
                    <div style="color:rgba(255,255,255,.82);">{{ $user->email }}</div>
                    <span class="profile-role">{{ $user->role->name ?? 'User' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card profile-form-card shadow-sm">
            <div class="card-header">
                <strong><i class="bi bi-person-gear me-2 text-primary"></i>Edit Profile</strong>
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('updateprofile', $user->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                    <div class="col-12 mb-3">
                        <input type="file" name="profile_image" id="profile-image-input" class="visually-hidden" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Click your profile photo to choose and crop an image. JPG, PNG, or WebP, up to 3 MB.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="{{ $user->role->name ?? 'User' }}" readonly aria-readonly="true">
                    </div>
                    </div>
                    <button type="submit" class="btn btn-primary profile-save-btn"><i class="bi bi-check2 me-1"></i>Save Changes</button>
                </form>

            </div>
        </div>

    </div>
</div>
</div>

<div class="modal fade" id="profileCropModal" tabindex="-1" aria-labelledby="profileCropTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header">
                <h5 class="modal-title" id="profileCropTitle">Crop profile photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column align-items-center gap-3">
                <p class="small text-muted mb-0">Drag the image to position it inside the square.</p>
                <div id="profile-crop-stage" class="profile-crop-stage" aria-label="Square profile photo crop area">
                    <img id="profile-crop-image" alt="Image to crop">
                </div>
                <label class="w-100 small fw-semibold" for="profile-crop-zoom">Zoom</label>
                <input type="range" id="profile-crop-zoom" class="form-range w-100" min="1" max="3" step="0.01" value="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="profile-crop-apply" class="btn btn-primary">Use this photo</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const trigger = document.getElementById('profile-avatar-trigger');
    const input = document.getElementById('profile-image-input');
    const preview = document.getElementById('profile-avatar-preview');
    const placeholder = document.getElementById('profile-avatar-placeholder');
    const modalElement = document.getElementById('profileCropModal');
    const cropImage = document.getElementById('profile-crop-image');
    const stage = document.getElementById('profile-crop-stage');
    const zoom = document.getElementById('profile-crop-zoom');
    const applyButton = document.getElementById('profile-crop-apply');

    if (!trigger || !input || !preview || !modalElement || !window.bootstrap) return;

    const cropModal = new bootstrap.Modal(modalElement);
    const stageSize = 320;
    let objectUrl = null;
    let baseScale = 1;
    let imageScale = 1;
    let imageX = 0;
    let imageY = 0;
    let dragging = false;
    let dragX = 0;
    let dragY = 0;

    function placeCropImage(center) {
        imageScale = baseScale * Number(zoom.value || 1);
        const width = cropImage.naturalWidth * imageScale;
        const height = cropImage.naturalHeight * imageScale;
        if (center) {
            imageX = (stageSize - width) / 2;
            imageY = (stageSize - height) / 2;
        }
        imageX = Math.min(0, Math.max(stageSize - width, imageX));
        imageY = Math.min(0, Math.max(stageSize - height, imageY));
        cropImage.style.width = width + 'px';
        cropImage.style.height = height + 'px';
        cropImage.style.left = imageX + 'px';
        cropImage.style.top = imageY + 'px';
    }

    trigger.addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) return;
        if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
            input.value = '';
            return;
        }
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        cropImage.onload = function () {
            baseScale = Math.max(stageSize / cropImage.naturalWidth, stageSize / cropImage.naturalHeight);
            zoom.value = '1';
            placeCropImage(true);
            cropModal.show();
        };
        cropImage.src = objectUrl;
    });

    zoom.addEventListener('input', function () { placeCropImage(true); });
    stage.addEventListener('wheel', function (event) {
        event.preventDefault();
        const currentZoom = Number(zoom.value || 1);
        zoom.value = String(Math.min(3, Math.max(1, currentZoom + (event.deltaY < 0 ? 0.1 : -0.1))));
        placeCropImage(true);
    }, { passive: false });
    stage.addEventListener('pointerdown', function (event) {
        dragging = true;
        dragX = event.clientX - imageX;
        dragY = event.clientY - imageY;
        stage.setPointerCapture(event.pointerId);
    });
    stage.addEventListener('pointermove', function (event) {
        if (!dragging) return;
        imageX = event.clientX - dragX;
        imageY = event.clientY - dragY;
        placeCropImage(false);
    });
    stage.addEventListener('pointerup', function () { dragging = false; });
    stage.addEventListener('pointercancel', function () { dragging = false; });

    applyButton.addEventListener('click', function () {
        const sourceX = -imageX / imageScale;
        const sourceY = -imageY / imageScale;
        const sourceSize = stageSize / imageScale;
        const canvas = document.createElement('canvas');
        canvas.width = 600;
        canvas.height = 600;
        const context = canvas.getContext('2d');
        context.drawImage(cropImage, sourceX, sourceY, sourceSize, sourceSize, 0, 0, 600, 600);
        canvas.toBlob(function (blob) {
            if (!blob) return;
            const croppedFile = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' });
            const transfer = new DataTransfer();
            transfer.items.add(croppedFile);
            input.files = transfer.files;
            preview.src = URL.createObjectURL(blob);
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
            cropModal.hide();
        }, 'image/jpeg', 0.92);
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        if (!input.files || !input.files.length) input.value = '';
    });
});
</script>

@endsection
