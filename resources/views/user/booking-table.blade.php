<div class="card shadow-sm">

    <div class="card-body p-0">

        <table class="table table-hover mb-0">

            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Sân</th>
                    <th>Ngày đặt</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>

            <tbody>

                @forelse($myBookings as $b)

                    <tr>
                        <td>{{ $b->id }}</td>
                        <td>{{ $b->field->name ?? '---' }}</td>
                        <td>{{ $b->created_at->format('d/m/Y') }}</td>

                        <td>
                            @if($b->order->status == 'pending')
                                <span class="badge bg-warning">Chờ xử lý</span>
                            @elseif($b->order->status == 'paid')
                                <span class="badge bg-success">Đã thanh toán</span>
                            @else
                                <span class="badge bg-danger">Đã huỷ</span>
                            @endif
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Không có booking nào
                        </td>
                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

</div>
 <div class="col-md-4">

            <div class="card shadow-sm">
                <div class="card-body text-center">

                    <h5>🎁 Khuyến mãi</h5>
                    <p>Giảm 20% dịch vụ hôm nay</p>

                    <a href="{{ route('user.services') }}"
                       class="btn btn-primary btn-sm">
                        Xem dịch vụ
                    </a>

                </div>
            </div>

            <!-- STATS -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">

                    <h6>📊 Thống kê nhanh</h6>

                    <p class="mb-1">
                        Tổng:
                        <span id="total-count">0</span>
                    </p>

                    <p class="mb-1">
                        Chờ xử lý:
                        <span id="pending-count">0</span>
                    </p>

                    <p class="mb-0">
                        Đã thanh toán:
                        <span id="paid-count">0</span>
                    </p>

                </div>
            </div>

        </div>
    </div>

</div>
<!-- PAGINATION -->
<div class="mt-3">
    {{ $myBookings->links('pagination::bootstrap-5') }}
</div>