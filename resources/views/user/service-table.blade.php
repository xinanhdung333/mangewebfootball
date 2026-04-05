  @if($myServices && count($myServices) > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Ảnh</th>
                        <th>Dịch vụ</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Ngày mua</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>             
                    @foreach($myServices as $service)
                        <tr>
                            <td>
                                <img src="{{ !empty($service->service->image) ? asset('uploads/services/' . $service->service->image) : asset('images/default.png') }}" 
                                     alt="{{ $service->service->name ?? 'Dịch vụ' }}"
                                     class="imgservice"
                                     style="border-radius: 6px; object-fit: cover;">
                            </td>
                            <td><strong>{{ $service->service->name ?? 'Dịch vụ' }}</strong></td>
                            <td>{{ $service->quantity }}</td>
                            <td>
                                <span class="fw-bold text-success">
                                    {{ number_format($service->quantity * $service->service->price, 0, ',', '.') }} VNĐ
                                </span>
                            </td>
                            <td>{{ Carbon\Carbon::parse($service->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'secondary',
                                        'confirmed' => 'info',
                                        'processing' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusTexts = [
                                        'pending' => 'Chờ xử lý',
                                        'confirmed' => 'Đã xác nhận',
                                        'processing' => 'Đang xử lý',
                                        'completed' => 'Hoàn tất',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$service['status']] ?? 'dark' }}">
                                    {{ $statusTexts[$service['status']] ?? 'Không xác định' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('user.orderDetail', ['id' => $service['order_id']]) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
               {{ $myServices->links('pagination::bootstrap-5') }}
               </div>
    @else
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
            <p class="mt-3">Bạn chưa mua dịch vụ nào. <a href="{{ route('user.services') }}">Khám phá dịch vụ</a></p>
        </div>
    @endif
</div>

