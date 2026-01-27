@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-grid"></i> Quản lý dịch vụ</h1>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row mb-4">
    <div class="col-md-12">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-lg"></i> Thêm dịch vụ
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên dịch vụ</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($services->count() > 0)
                        @foreach ($services as $service)
                            <tr>
                                <td>{{ $service->id }}</td>
                                <td>
                                    @if ($service->image)
                                        <img src="{{ asset('uploads/services/' . $service->image) }}"
                                            style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                                    @else
                                        <span class="text-muted">Không có</span>
                                    @endif
                                </td>
                                <td>{{ htmlspecialchars($service->name) }}</td>
                                <td>{{ number_format($service->price, 0, ',', '.') }}đ</td>
                                <td>{{ $service->quantity }}</td>
                                <td>
                                    @if ($service->status == 'active')
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Ngưng hoạt động</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editServiceModal"
                                            onclick="editService(@json($service))">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <form method="POST" action="{{ route('boss.delete.service') }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $service->id }}">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">Không có dịch vụ nào</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL THÊM DỊCH VỤ -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('boss.store.service') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm dịch vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tên dịch vụ</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label>Giá</label>
                        <input type="number" class="form-control" name="price" step="1000" required>
                    </div>

                    <div class="mb-3">
                        <label>Số lượng</label>
                        <input type="number" class="form-control" name="quantity" min="0" value="0" required>
                    </div>

                    <div class="mb-3">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Ngưng hoạt động</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Ảnh</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL SỬA DỊCH VỤ -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('boss.update.service') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Sửa dịch vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label>Tên dịch vụ</label>
                        <input id="edit_name" name="name" type="text" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Giá</label>
                        <input id="edit_price" name="price" type="number" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Số lượng</label>
                        <input id="edit_quantity" name="quantity" type="number" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label>Trạng thái</label>
                        <select id="edit_status" name="status" class="form-control" required>
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Ngưng hoạt động</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Ảnh hiện tại</label><br>
                        <img id="edit_preview" src="" style="width:100px;height:100px;border-radius:6px;object-fit:cover;">
                    </div>

                    <div class="mb-3">
                        <label>Đổi ảnh</label>
                        <input type="file" name="image" class="form-control" accept="image/*" id="edit_image_input">
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

<script>
function editService(service) {
    document.getElementById('edit_id').value = service.id;
    document.getElementById('edit_name').value = service.name;
    document.getElementById('edit_price').value = service.price;
    document.getElementById('edit_quantity').value = service.quantity || 0;
    document.getElementById('edit_status').value = service.status;

    document.getElementById('edit_preview').src = service.image
        ? "{{ asset('uploads/services/') }}/" + service.image + "?t=" + new Date().getTime()
        : "";

    document.getElementById('edit_image_input').value = "";
}

// Preview khi chọn file mới
document.getElementById('edit_image_input').addEventListener('change', function(event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('edit_preview').src = URL.createObjectURL(file);
    }
});
</script>
@endsection
