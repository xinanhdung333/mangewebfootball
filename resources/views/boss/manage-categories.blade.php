@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-tags"></i> Quản lý danh mục</h1>
        <p class="text-muted mb-0">Sắp xếp sản phẩm theo danh mục.</p>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="bi bi-plus-lg"></i> Thêm danh mục</button>
</div>

@include('partials.category-alerts')

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-3">#</th><th>Tên danh mục</th><th>Số sản phẩm</th><th class="text-end pe-3">Thao tác</th></tr></thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td class="ps-3">{{ $category->id }}</td>
                    <td><span class="badge bg-info text-dark">{{ $category->name }}</span>@if($category->id === 1) <small class="text-muted ms-2">Mặc định</small>@endif</td>
                    <td>{{ $category->services_count }}</td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal" onclick='editCategory(@json($category))'><i class="bi bi-pencil"></i> Sửa</button>
                        @if($category->id !== 1)
                            <form method="POST" action="{{ route('boss.delete.category') }}" class="d-inline">@csrf<input type="hidden" name="id" value="{{ $category->id }}"><button class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa danh mục này? Sản phẩm sẽ chuyển về Tổng hợp.')"><i class="bi bi-trash"></i> Xóa</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Chưa có danh mục nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())<div class="card-footer bg-white">{{ $categories->links() }}</div>@endif
</div>

@include('partials.category-modals', ['prefix' => 'boss'])
@endsection
