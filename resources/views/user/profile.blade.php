@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-person"></i> Hồ sơ cá nhân</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card text-center mb-3">
            <div class="card-body">
                <img src="{{ !empty($avatar) ? asset('uploads/avatars/'.$avatar) : asset('assets/images/default.png') }}" 
                     alt="Avatar" class="rounded-circle mb-2" style="width:120px;height:120px;object-fit:cover;">
                <h5>{{ Auth::user()->name }}</h5>
                <p>{{ Auth::user()->email }}</p>
                <p><strong>Vai trò:</strong> {{ Auth::user()->role=='admin'?'Quản lý':'Người dùng' }}</p>
                <p><strong>Ngày tạo:</strong> {{ formatDateTime(Auth::user()->created_at) }}</p>
            </div>
        </div>


        <div class="card d-none d-md-block" style="max-height:500px; overflow-y:auto;">
            <div class="card-body">
                <h5 class="mb-3">Lịch sử giao dịch</h5>

                <h6 class="text-primary"><i class="bi bi-calendar-check"></i> Đặt sân</h6>
                @if($bookingHistory && count($bookingHistory) > 0)
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($bookingHistory as $b)
                            <li class="list-group-item">
                                <strong>{{ $b->field_name ?? 'N/A' }}</strong><br>
                                <small>
                                    {{ date('d/m/Y', strtotime($b->booking_date)) }} |
                                    {{ $b->start_time }} - {{ $b->end_time }}
                                </small><br>
                                <span class="fw-bold text-success">{{ formatCurrency($b->total_price) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Không có lịch sử đặt sân.</p>
                @endif

                <h6 class="text-primary"><i class="bi bi-bag-check"></i> Mua dịch vụ</h6>
                @if($serviceHistory && count($serviceHistory) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($serviceHistory as $s)
                            <li class="list-group-item">
                                <strong>{{ $s->service_name ?? 'N/A' }}</strong><br>
                                <small>{{ date('d/m/Y H:i', strtotime($s->created_at)) }}</small><br>
                                <span class="fw-bold text-success">{{ formatCurrency($s->total) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Không có lịch sử dịch vụ.</p>
                @endif
            </div>
        </div>
    </div>


    <div class="col-md-8">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <h5>Cập nhật thông tin</h5>

                <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label>Họ tên</label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', Auth::user()->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="{{ old('email', Auth::user()->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" 
                               value="{{ old('phone', Auth::user()->phone) }}">
                    </div>

                    <div class="mb-3">
                        <label>Upload avatar</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                    </div>

                    <hr>
                    <h6>Đổi mật khẩu (tùy chọn)</h6>

                    <div class="mb-3">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="new_password_confirmation" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Cập nhật
                    </button>
                </form>
            </div>
        </div>

        <!-- Phần Quản lý Địa chỉ -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>
                        <i class="bi bi-geo-alt"></i> Địa chỉ giao hàng
                    </h5>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="bi bi-plus-lg"></i> Thêm địa chỉ
                    </button>
                </div>

                @if($addresses && count($addresses) > 0)
                    <div class="list-group">
                        @foreach($addresses as $address)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            {{ $address->name ?? Auth::user()->name }}
                                            @if($address->is_default)
                                                <span class="badge bg-primary">Mặc định</span>
                                            @endif
                                        </h6>
                                        <p class="mb-1 text-muted">
                                            {{ $address->street_address }}
                                            @if($address->ward)
                                                , {{ $address->ward }}
                                            @endif
                                            @if($address->district)
                                                , {{ $address->district }}
                                            @endif
                                            , {{ $address->city }}
                                            @if($address->postal_code)
                                                - {{ $address->postal_code }}
                                            @endif
                                        </p>
                                        @if($address->phone)
                                            <p class="mb-0"><small><i class="bi bi-telephone"></i> {{ $address->phone }}</small></p>
                                        @endif
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAddressModal{{ $address->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('user.address.delete', $address->id) }}" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa địa chỉ này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-4">
                        <i class="bi bi-inbox"></i><br>
                        Chưa có địa chỉ nào. Hãy thêm địa chỉ của bạn!
                    </p>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- Modals ở ngoài container để tránh lỗi layout nháy của Bootstrap -->
@if($addresses && count($addresses) > 0)
    @foreach($addresses as $address)
        <div class="modal fade" id="editAddressModal{{ $address->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chỉnh sửa địa chỉ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('user.address.update', $address->id) }}" class="ghn-address-form"
                          data-address-province="{{ $address->ghn_province_id ?? '' }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Tên người nhận</label>
                                <input type="text" name="name" class="form-control" value="{{ $address->name ?? Auth::user()->name }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="{{ $address->phone }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ chi tiết *</label>
                                <input type="text" name="street_address" class="form-control" value="{{ $address->street_address }}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phường/Xã GHN *</label>
                                        <select name="ghn_ward_code" class="form-select ghn-ward" data-value="{{ $address->ghn_ward_code ?? '' }}" required><option value="">Chọn quận trước</option></select>
                                        <input type="hidden" name="ward" class="ghn-ward-name" value="{{ $address->ward }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quận/Huyện GHN *</label>
                                    <select name="ghn_district_id" class="form-select ghn-district" data-value="{{ $address->ghn_district_id ?? '' }}" required><option value="">Chọn tỉnh trước</option></select>
                                    <input type="hidden" name="district" class="ghn-district-name" value="{{ $address->district }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Tỉnh/Thành phố GHN *</label>
                                    <select name="ghn_province_id" class="form-select ghn-province" required><option value="">Đang tải...</option></select>
                                    <input type="hidden" name="city" class="ghn-province-name" value="{{ $address->city }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Mã bưu điện</label>
                                    <input type="text" name="postal_code" class="form-control" value="{{ $address->postal_code }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-geocode">
                                    <i class="bi bi-geo-alt"></i> Tìm toạ độ tự động
                                </button>
                                <span class="geocode-msg ms-2 text-muted" style="font-size: 0.85rem;">Bấm để lấy toạ độ giao hàng</span>
                                <input type="hidden" name="lat" class="lat-input" value="{{ $address->lat }}">
                                <input type="hidden" name="lng" class="lng-input" value="{{ $address->lng }}">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_default" class="form-check-input" value="1" @if($address->is_default) checked @endif>
                                <label class="form-check-label">Đặt làm địa chỉ mặc định</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- Modal thêm địa chỉ mới -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm địa chỉ mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('user.address.store') }}" class="ghn-address-form">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên người nhận</label>
                        <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="{{ Auth::user()->phone }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ chi tiết *</label>
                        <input type="text" name="street_address" class="form-control" placeholder="Số nhà, tên đường..." required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phường/Xã GHN *</label>
                            <select name="ghn_ward_code" class="form-select ghn-ward" required><option value="">Chọn quận trước</option></select>
                            <input type="hidden" name="ward" class="ghn-ward-name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quận/Huyện GHN *</label>
                            <select name="ghn_district_id" class="form-select ghn-district" required><option value="">Chọn tỉnh trước</option></select>
                            <input type="hidden" name="district" class="ghn-district-name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Tỉnh/Thành phố GHN *</label>
                            <select name="ghn_province_id" class="form-select ghn-province" required><option value="">Đang tải...</option></select>
                            <input type="hidden" name="city" class="ghn-province-name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mã bưu điện</label>
                            <input type="text" name="postal_code" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-geocode">
                            <i class="bi bi-geo-alt"></i> Tìm toạ độ tự động
                        </button>
                        <span class="geocode-msg ms-2 text-muted" style="font-size: 0.85rem;">Bấm để lấy toạ độ giao hàng</span>
                        <input type="hidden" name="lat" class="lat-input">
                        <input type="hidden" name="lng" class="lng-input">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_default" class="form-check-input" value="1">
                        <label class="form-check-label">Đặt làm địa chỉ mặc định</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Thêm địa chỉ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ghnRoutes = {
        provinces: @json(route('user.ghn.provinces')),
        districts: @json(route('user.ghn.districts')),
        wards: @json(route('user.ghn.wards'))
    };
    const loadGhn = url => fetch(url, {headers: {'Accept': 'application/json'}})
        .then(response => {
            if (!response.ok) throw new Error('Không tải được dữ liệu địa chỉ GHN.');
            return response.json();
        });
    const fillGhn = (select, items, valueKey, labelKey, placeholder) => {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[labelKey];
            select.appendChild(option);
        });
        select.disabled = false;
    };
    const setGhnLabel = (select, input) => {
        input.value = select.options[select.selectedIndex]?.text || '';
    };

    document.querySelectorAll('.ghn-address-form').forEach(async form => {
        const province = form.querySelector('.ghn-province');
        const district = form.querySelector('.ghn-district');
        const ward = form.querySelector('.ghn-ward');
        const provinceName = form.querySelector('.ghn-province-name');
        const districtName = form.querySelector('.ghn-district-name');
        const wardName = form.querySelector('.ghn-ward-name');
        const savedProvince = @json(optional($addresses->first())->ghn_province_id);
        try {
            const provinces = await loadGhn(ghnRoutes.provinces);
            fillGhn(province, provinces, 'ProvinceID', 'ProvinceName', 'Chọn tỉnh/thành');
            const initialProvince = form.dataset.addressProvince || province.dataset.value || '';
            if (initialProvince) {
                province.value = initialProvince;
                const districts = await loadGhn(`${ghnRoutes.districts}?province_id=${initialProvince}`);
                fillGhn(district, districts, 'DistrictID', 'DistrictName', 'Chọn quận/huyện');
                if (district.dataset.value) {
                    district.value = district.dataset.value;
                    const wards = await loadGhn(`${ghnRoutes.wards}?district_id=${district.value}`);
                    fillGhn(ward, wards, 'WardCode', 'WardName', 'Chọn phường/xã');
                    ward.value = ward.dataset.value || '';
                }
            }
            setGhnLabel(province, provinceName);
            setGhnLabel(district, districtName);
            setGhnLabel(ward, wardName);
        } catch (error) {
            form.querySelector('.geocode-msg').textContent = error.message;
        }
        province.addEventListener('change', async () => {
            setGhnLabel(province, provinceName);
            district.disabled = true; ward.disabled = true;
            const districts = await loadGhn(`${ghnRoutes.districts}?province_id=${province.value}`);
            fillGhn(district, districts, 'DistrictID', 'DistrictName', 'Chọn quận/huyện');
            ward.innerHTML = '<option value="">Chọn quận/huyện trước</option>';
            districtName.value = ''; wardName.value = '';
        });
        district.addEventListener('change', async () => {
            setGhnLabel(district, districtName);
            ward.disabled = true;
            const wards = await loadGhn(`${ghnRoutes.wards}?district_id=${district.value}`);
            fillGhn(ward, wards, 'WardCode', 'WardName', 'Chọn phường/xã');
            wardName.value = '';
        });
        ward.addEventListener('change', () => setGhnLabel(ward, wardName));
    });

    document.querySelectorAll('.btn-geocode').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = this.closest('form');
            const ward    = form.querySelector('.ghn-ward-name')?.value.trim() || '';
            const district= form.querySelector('.ghn-district-name')?.value.trim() || '';
            const city    = form.querySelector('.ghn-province-name').value.trim();
            const msg     = form.querySelector('.geocode-msg');
            const latInput= form.querySelector('.lat-input');
            const lngInput= form.querySelector('.lng-input');

            if(!city) {
                msg.innerHTML = '<span class="text-danger">Vui lòng nhập ít nhất Tỉnh/Thành phố.</span>';
                return;
            }

            const origHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            this.disabled  = true;
            msg.innerHTML  = '<span class="text-info">Đang tìm kiếm...</span>';

            // Chiến lược: thử từ chi tiết → rộng dần
            const attempts = [];
            if (ward && district) attempts.push([ward, district, city, 'Việt Nam'].join(', '));
            if (district)         attempts.push([district, city, 'Việt Nam'].join(', '));
                                  attempts.push([city, 'Việt Nam'].join(', '));

            const tryNext = (index) => {
                if (index >= attempts.length) {
                    msg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Không tìm thấy toạ độ. Hãy nhập tên phường/quận đầy đủ.</span>';
                    this.innerHTML = origHtml;
                    this.disabled  = false;
                    return;
                }
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(attempts[index]))
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            latInput.value = data[0].lat;
                            lngInput.value = data[0].lon;
                            if (index === 0) {
                                msg.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Đã lấy được toạ độ chính xác!</span>';
                            } else {
                                msg.innerHTML = '<span class="text-warning"><i class="bi bi-check-circle"></i> Đã lấy được toạ độ cấp ' + (index === 1 ? 'quận/huyện' : 'thành phố') + ' — đủ dùng để tính phí giao hàng.</span>';
                            }
                            this.innerHTML = origHtml;
                            this.disabled  = false;
                        } else {
                            tryNext(index + 1);
                        }
                    })
                    .catch(() => {
                        msg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Lỗi kết nối bản đồ.</span>';
                        this.innerHTML = origHtml;
                        this.disabled  = false;
                    });
            };

            tryNext(0);
        });
    });
});
</script>
@endsection
