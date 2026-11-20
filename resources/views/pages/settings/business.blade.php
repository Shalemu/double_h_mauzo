@extends('main')

@section('title', 'Settings')

@section('content')
<div class="container-fluid py-4">
    @include('pages.settings._tabs')

    <form method="POST" action="{{ route('settings.business.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-building me-2"></i>Business Profile</h5>
                        <p class="settings-help">Shown on the sidebar, page titles and printed receipts.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                                <input type="text" name="business_name" id="business_name" class="form-control @error('business_name') is-invalid @enderror"
                                       value="{{ old('business_name', $values['business_name']) }}" maxlength="150" required>
                                @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $values['phone']) }}" maxlength="50" placeholder="e.g. 0712 345 678">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $values['email']) }}" maxlength="150">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror"
                                       value="{{ old('address', $values['address']) }}" maxlength="255" placeholder="e.g. Kariakoo, Dar es Salaam">
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tin" class="form-label">TIN</label>
                                <input type="text" name="tin" id="tin" class="form-control @error('tin') is-invalid @enderror"
                                       value="{{ old('tin', $values['tin']) }}" maxlength="50" placeholder="Taxpayer Identification Number">
                                @error('tin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="vrn" class="form-label">VRN</label>
                                <input type="text" name="vrn" id="vrn" class="form-control @error('vrn') is-invalid @enderror"
                                       value="{{ old('vrn', $values['vrn']) }}" maxlength="50" placeholder="VAT Registration Number (if registered)">
                                @error('vrn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-image me-2"></i>Logo</h5>
                        <p class="settings-help">PNG, JPG or WEBP, up to 2 MB. A square image works best.</p>
                    </div>
                    <div class="card-body text-center">
                        <div class="border rounded d-inline-flex align-items-center justify-content-center bg-white p-2 mb-3" style="width:140px;height:140px">
                            <img src="{{ $logoUrl }}" id="logoPreview" alt="Business logo" style="max-width:100%;max-height:100%;object-fit:contain">
                        </div>
                        <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                        @error('logo')<div class="invalid-feedback text-start">{{ $message }}</div>@enderror
                        @if($hasCustomLogo)
                            <div class="form-check text-start mt-2">
                                <input type="hidden" name="remove_logo" value="0">
                                <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                                <label class="form-check-label" for="remove_logo">Remove uploaded logo (use the default)</label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Save Business Profile</button>
        </div>
    </form>
</div>

<script>
document.getElementById('logo')?.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('logoPreview').src = e.target.result; };
    reader.readAsDataURL(file);
});
</script>
@endsection
