@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1">Quản lý trang giới thiệu</h2>
            <small class="text-muted">Chỉnh sửa nội dung hiển thị trên trang About</small>
        </div>
        <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại cài đặt
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.settings.about.store') }}" method="POST" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tiêu đề hero</label>
                    <input type="text" name="about_hero_title" class="form-control" value="{{ old('about_hero_title', $settings['about_hero_title'] ?? 'Về chúng tôi') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Phụ đề hero</label>
                    <input type="text" name="about_hero_subtitle" class="form-control" value="{{ old('about_hero_subtitle', $settings['about_hero_subtitle'] ?? 'Sứ mệnh – Tầm nhìn – Giá trị của SportsHub') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Tiêu đề giới thiệu</label>
                    <input type="text" name="about_intro_title" class="form-control" value="{{ old('about_intro_title', $settings['about_intro_title'] ?? 'Về SportsHub') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Lead giới thiệu</label>
                    <input type="text" name="about_intro_lead" class="form-control" value="{{ old('about_intro_lead', $settings['about_intro_lead'] ?? 'SportsHub là nền tảng đặt sân bóng và dịch vụ đi kèm, kết nối cộng đồng yêu bóng đá với các sân chất lượng.') }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Nội dung giới thiệu 1</label>
                    <textarea name="about_intro_paragraph_1" class="form-control" rows="4">{{ old('about_intro_paragraph_1', $settings['about_intro_paragraph_1'] ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Nội dung giới thiệu 2</label>
                    <textarea name="about_intro_paragraph_2" class="form-control" rows="4">{{ old('about_intro_paragraph_2', $settings['about_intro_paragraph_2'] ?? '') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Tiêu đề lịch sử</label>
                    <input type="text" name="about_history_title" class="form-control" value="{{ old('about_history_title', $settings['about_history_title'] ?? 'Lịch sử ra đời') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tiêu đề vai trò</label>
                    <input type="text" name="about_role_title" class="form-control" value="{{ old('about_role_title', $settings['about_role_title'] ?? 'Vai trò trong cuộc sống') }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Lịch sử 1</label>
                    <textarea name="about_history_paragraph_1" class="form-control" rows="4">{{ old('about_history_paragraph_1', $settings['about_history_paragraph_1'] ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Lịch sử 2</label>
                    <textarea name="about_history_paragraph_2" class="form-control" rows="4">{{ old('about_history_paragraph_2', $settings['about_history_paragraph_2'] ?? '') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Tiêu đề sứ mệnh</label>
                    <input type="text" name="about_mission_title" class="form-control" value="{{ old('about_mission_title', $settings['about_mission_title'] ?? 'Sứ mệnh') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tiêu đề tầm nhìn</label>
                    <input type="text" name="about_vision_title" class="form-control" value="{{ old('about_vision_title', $settings['about_vision_title'] ?? 'Tầm nhìn') }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Nội dung sứ mệnh</label>
                    <textarea name="about_mission_text" class="form-control" rows="4">{{ old('about_mission_text', $settings['about_mission_text'] ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Nội dung tầm nhìn</label>
                    <textarea name="about_vision_text" class="form-control" rows="4">{{ old('about_vision_text', $settings['about_vision_text'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary" style="background:#ee4d2d; border-color:#ee4d2d;">
                    <i class="bi bi-check-circle"></i> Lưu thay đổi
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
