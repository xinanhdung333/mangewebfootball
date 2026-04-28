@extends('layouts.app')

@section('content')
<div class="container">
    <h2>⚙️ Cài đặt giá & giảm giá</h2>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ================= FIELD PRICING ================= --}}
    <h4 class="mt-4">⚽ Giá sân theo khung giờ</h4>

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
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label>Đến</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label>Hệ số</label>
                        <input type="number" step="0.1" name="multiplier" class="form-control" required>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100">+ Thêm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
            <td>{{ $r->start_time }} - {{ $r->end_time }}</td>
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

    {{-- ================= SERVICE DISCOUNT ================= --}}
    <hr>
    <h4>🎯 Giảm giá dịch vụ</h4>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.service-discount.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-2">
                        <label>Dịch vụ</label>
                        <select name="service_id" class="form-control">
                            <option value="">Toàn bộ</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Từ</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label>Đến</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label>Hệ số</label>
                        <input type="number" step="0.1" name="multiplier" class="form-control" required>
                    </div>

                    {{-- 🔥 THÊM NOTE --}}
                    <div class="col-md-2">
                        <label>Lý do</label>
                        <input type="text" name="note" class="form-control" placeholder="VD: Sale 2/9">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">+ Thêm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered">
        <tr>
            <th>Dịch vụ</th>
            <th>Khung giờ</th>
            <th>Giảm / Tăng</th>
            <th>Lý do</th>
            <th></th>
        </tr>

        @foreach($serviceDiscounts as $d)
        <tr class="{{ $d->multiplier < 1 ? 'table-success' : 'table-warning' }}">
            <td>{{ $d->service->name ?? 'Toàn bộ' }}</td>
            <td>{{ $d->start_time }} - {{ $d->end_time }}</td>

            <td>
                @if($d->multiplier < 1)
                    <span class="text-success fw-bold">
                        -{{ (1 - $d->multiplier) * 100 }}%
                    </span>
                @else
                    <span class="text-danger fw-bold">
                        +{{ ($d->multiplier - 1) * 100 }}%
                    </span>
                @endif
            </td>

            {{-- 🔥 HIỂN THỊ NOTE --}}
            <td>
                @if($d->note)
                    <span class="badge bg-danger">
                        🔥 {{ $d->note }}
                    </span>
                @else
                    ---
                @endif
            </td>

            <td>
                <form method="POST" action="{{ route('admin.settings.service-discount.delete', $d->id) }}">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('Xoá?')" class="btn btn-danger btn-sm">Xoá</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>

</div>
@endsection