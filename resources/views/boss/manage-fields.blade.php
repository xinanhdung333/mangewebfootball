@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="bi bi-grid"></i> Quản lý sân</h1>
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
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFieldModal">
            <i class="bi bi-plus-lg"></i> Thêm sân
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
                        <th>Ảnh sân</th>
                        <th>Tên sân</th>
                        <th>Địa chỉ</th>
                        <th>Giá/giờ</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($fields->count() > 0)
                        @foreach ($fields as $field)
                            <tr>
                                <td>{{ $field->id }}</td>
                                <td>
                                    @if ($field->image)
                                        <img src="{{ asset('uploads/fields/' . $field->image) }}"
                                            style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                                    @else
                                        <span class="text-muted">Không có</span>
                                    @endif
                                </td>
                                <td>{{ htmlspecialchars($field->name) }}</td>
                                <td>{{ htmlspecialchars($field->location) }}</td>
                                <td>{{ number_format($field->price_per_hour, 0, ',', '.') }}đ</td>

                                <td>
                                    @if ($field->status == 'active')
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Ngưng hoạt động</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editFieldModal"
                                            onclick="editField(@json($field))">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                    <form method="POST" action="{{ route('boss.delete.field') }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $field->id }}">
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
                            <td colspan="7" class="text-center">Không có sân nào</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============== MODAL THÊM ============== -->
<div class="modal fade" id="addFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('boss.store.field') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sân</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tên sân</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label>Địa chỉ</label>
                        <input type="text" class="form-control" name="location" required>
                    </div>

                    <div class="mb-3">
                        <label>Mô tả</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Giá/giờ</label>
                        <input type="number" class="form-control" name="price_per_hour" step="1000" required>
                    </div>

                    <div class="mb-3">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Ngưng hoạt động</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Ảnh sân</label>
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

<!-- ============== MODAL SỬA ============== -->
<div class="modal fade" id="editFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('boss.update.field') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Sửa sân</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label>Tên sân</label>
                        <input id="edit_name" name="name" type="text" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Địa chỉ</label>
                        <input id="edit_location" name="location" type="text" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Mô tả</label>
                        <textarea id="edit_description" name="description" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Giá/giờ</label>
                        <input id="edit_price" name="price_per_hour" type="number" class="form-control" required>
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
function editField(field) {
    document.getElementById('edit_id').value = field.id;
    document.getElementById('edit_name').value = field.name;
    document.getElementById('edit_location').value = field.location;
    document.getElementById('edit_description').value = field.description;
    document.getElementById('edit_price').value = field.price_per_hour;
    document.getElementById('edit_status').value = field.status;

    document.getElementById('edit_preview').src = field.image
        ? "{{ asset('uploads/fields/') }}/" + field.image + "?t=" + new Date().getTime()
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
