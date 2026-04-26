@extends('layouts.app')

@section('content')
<div class="container">
    <h2>⚙️ Cài đặt giá theo khung giờ</h2>
@if ($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- FORM THÊM --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.pricing.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-3">
                        <label>Sân</label>
                        <select name="field_id" class="form-control">
                            <option value="">Toàn bộ</option>
                            @foreach($fields as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Từ</label>
                        <input type="time" name="start_time" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>Đến</label>
                        <input type="time" name="end_time" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>Hệ số</label>
                        <input type="number" step="0.1" name="multiplier" class="form-control">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100">+ Thêm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- LIST --}}
    <table class="table table-bordered">
        <tr>
            <th>Sân</th>
            <th>Khung giờ</th>
            <th>Hệ số</th>
            <th></th>
        </tr>

        @foreach($rules as $r)
        <tr>
            <td>{{ $r->field->name ?? 'Toàn bộ' }}</td>
            <td>{{ $r->start_time }}h - {{ $r->end_time }}h</td>
            <td>x{{ $r->multiplier }}</td>
            <td>
                <form method="POST" action="{{ route('admin.settings.pricing.delete', $r->id) }}">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('Xoá?')" class="btn btn-danger btn-sm">Xoá</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection     