@extends('layouts.app')

@section('content')
@php
    $activeFilter = request('filter', 'all');
    $filters = [
        'all' => ['label' => 'Tất cả', 'icon' => 'bi-grid'],
        'ending' => ['label' => 'Sắp hết hạn', 'icon' => 'bi-clock'],
        'low_min' => ['label' => 'Dễ dùng', 'icon' => 'bi-lightning-charge'],
    ];
@endphp

<style>
    .voucher-page {
        max-width: 1120px;
        margin: 0 auto;
        padding: clamp(12px, 2vw, 24px) 0 36px;
    }

    .voucher-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        align-items: end;
        padding: clamp(16px, 2.2vw, 24px);
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, rgba(238, 77, 45, .96), rgba(255, 115, 55, .94));
        box-shadow: 0 16px 36px rgba(238, 77, 45, .22);
    }

    .voucher-hero h1 {
        margin: 0 0 8px;
        font-size: clamp(1.55rem, 4vw, 2.45rem);
        line-height: 1.15;
        font-weight: 800;
    }

    .voucher-hero p {
        margin: 0;
        max-width: 620px;
        color: rgba(255,255,255,.9);
        font-size: clamp(.95rem, 2vw, 1.05rem);
    }

    .voucher-hero-icon {
        width: clamp(78px, 12vw, 128px);
        aspect-ratio: 1;
        border: 2px dashed rgba(255,255,255,.72);
        border-radius: 8px;
        display: grid;
        place-items: center;
        background: rgba(255,255,255,.12);
        font-size: clamp(2rem, 6vw, 3.4rem);
    }

    .voucher-toolbar {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto;
        gap: 14px;
        align-items: center;
        margin: 18px 0 14px;
    }

    .voucher-search {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        padding: 0 14px;
        border: 1px solid #e6e8ef;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
    }

    .voucher-search input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        font-size: .98rem;
    }

    .voucher-filter {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .voucher-filter a {
        flex: 0 0 auto;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 14px;
        border: 1px solid #e6e8ef;
        border-radius: 8px;
        color: #3f4757;
        background: #fff;
        text-decoration: none;
        font-weight: 700;
        white-space: nowrap;
    }

    .voucher-filter a.active {
        color: #ee4d2d;
        border-color: #ee4d2d;
        background: #fff5f1;
    }

    .voucher-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 14px;
    }

    .voucher-card {
        position: relative;
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr);
        min-height: 132px;
        border: 1px solid #edf0f5;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .08);
    }

    .voucher-card::before,
    .voucher-card::after {
        content: "";
        position: absolute;
        left: 84px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #f6f8fb;
        z-index: 2;
    }

    .voucher-card::before { top: -8px; }
    .voucher-card::after { bottom: -8px; }

    .voucher-left {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #fff;
        background: linear-gradient(160deg, #ee4d2d, #ff7337);
        text-align: center;
        padding: 12px 8px;
    }

    .voucher-left i {
        font-size: 1.65rem;
    }

    .voucher-left span {
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .voucher-body {
        min-width: 0;
        padding: 13px 14px;
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .voucher-value {
        color: #ee4d2d;
        font-size: clamp(1.18rem, 2vw, 1.45rem);
        font-weight: 900;
        line-height: 1.1;
    }

    .voucher-code-row {
        display: flex;
        gap: 8px;
        align-items: center;
        min-width: 0;
        margin-top: auto;
    }

    .voucher-code {
        flex: 1;
        min-width: 0;
        border: 1px dashed #ff8b66;
        border-radius: 8px;
        padding: 7px 9px;
        color: #0d47a1;
        background: #fff8f5;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .voucher-copy {
        flex: 0 0 auto;
        border: 0;
        border-radius: 8px;
        padding: 8px 11px;
        color: #fff;
        background: #ee4d2d;
        font-weight: 800;
    }

    .voucher-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 14px;
        color: #687385;
        font-size: .86rem;
    }

    .voucher-empty {
        border: 1px dashed #d8dee9;
        border-radius: 8px;
        padding: 36px 18px;
        text-align: center;
        background: #fff;
        color: #667085;
    }

    @media (max-width: 767px) {
        .voucher-page {
            padding-top: 8px;
        }

        .voucher-hero,
        .voucher-toolbar {
            grid-template-columns: 1fr;
        }

        .voucher-hero {
            padding: 15px;
        }

        .voucher-hero-icon {
            display: none;
        }

        .voucher-toolbar {
            gap: 10px;
            margin-top: 12px;
            margin-bottom: 12px;
        }

        .voucher-grid {
            gap: 10px;
        }

        .voucher-card {
            min-height: 118px;
        }
    }

    @media (max-width: 430px) {
        .voucher-grid {
            grid-template-columns: 1fr;
        }

        .voucher-card {
            grid-template-columns: 76px minmax(0, 1fr);
            min-height: 108px;
        }

        .voucher-card::before,
        .voucher-card::after {
            left: 68px;
        }

        .voucher-body {
            padding: 10px 11px;
            gap: 6px;
        }

        .voucher-left {
            padding: 10px 6px;
        }

        .voucher-left i {
            font-size: 1.35rem;
        }

        .voucher-left span {
            font-size: .68rem;
        }

        .voucher-code-row {
            gap: 6px;
        }

        .voucher-copy {
            padding: 7px 9px;
            font-size: .85rem;
        }

        .voucher-code {
            padding: 7px 8px;
            font-size: .9rem;
        }
    }
</style>

<section class="voucher-page">
    <div class="voucher-hero">
        <div>
            <h1>Kho voucher SportsHub</h1>
            <p>Lưu mã giảm giá trước khi thanh toán đơn hàng. Chọn mã phù hợp với giá trị đơn để tiết kiệm nhiều hơn.</p>
        </div>
        <div class="voucher-hero-icon" aria-hidden="true">
            <i class="bi bi-ticket-perforated"></i>
        </div>
    </div>

    <form class="voucher-toolbar" method="GET" action="{{ route('user.vouchers') }}">
        <label class="voucher-search">
            <i class="bi bi-search text-muted"></i>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Tìm mã voucher">
            <input type="hidden" name="filter" value="{{ $activeFilter }}">
        </label>

        <div class="voucher-filter" aria-label="Lọc voucher">
            @foreach($filters as $key => $filter)
                <a class="{{ $activeFilter === $key ? 'active' : '' }}"
                   href="{{ route('user.vouchers', array_filter(['filter' => $key === 'all' ? null : $key, 'q' => request('q')])) }}">
                    <i class="bi {{ $filter['icon'] }}"></i>{{ $filter['label'] }}
                </a>
            @endforeach
        </div>
    </form>

    @if($vouchers->count())
        <div class="voucher-grid">
            @foreach($vouchers as $voucher)
                @php
                    $type = $voucher->discount_type ?? 'fixed';
                    $expiresAt = $voucher->expires_at ? \Carbon\Carbon::parse($voucher->expires_at) : null;
                    $daysLeft = $expiresAt ? now()->diffInDays($expiresAt, false) : null;
                @endphp
                <article class="voucher-card">
                    <div class="voucher-left">
                        <i class="bi {{ $type === 'free_shipping' ? 'bi-truck' : 'bi-bag-heart' }}"></i>
                        <span>{{ $type === 'free_shipping' ? 'Freeship' : 'Voucher' }}</span>
                    </div>
                    <div class="voucher-body">
                        <div class="voucher-value">
                            @if($type === 'percentage')
                                -{{ rtrim(rtrim(number_format($voucher->discount_amount, 2, ',', '.'), '0'), ',') }}%
                            @elseif($type === 'free_shipping')
                                Freeship
                            @else
                                -{{ number_format($voucher->discount_amount, 0, ',', '.') }}đ
                            @endif
                        </div>
                        <div>Áp dụng cho đơn từ {{ number_format($voucher->min_order_amount, 0, ',', '.') }}đ</div>
                        @if($type === 'percentage' && $voucher->max_discount_amount)
                            <div class="small text-muted">Giảm tối đa {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}đ</div>
                        @endif

                        <div class="voucher-code-row">
                            <div class="voucher-code" title="{{ $voucher->code }}">{{ $voucher->code }}</div>
                            <button type="button" class="voucher-copy" data-code="{{ $voucher->code }}">
                                <i class="bi bi-copy"></i> Sao chép
                            </button>
                        </div>

                        <div class="voucher-meta">
                            <span><i class="bi bi-clock me-1"></i>{{ $expiresAt ? 'HSD ' . $expiresAt->format('d/m/Y H:i') : 'Không giới hạn thời gian' }}</span>
                            @if($daysLeft !== null && $daysLeft <= 7)
                                <span class="text-danger fw-semibold">Sắp hết hạn</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $vouchers->links() }}
        </div>
    @else
        <div class="voucher-empty">
            <i class="bi bi-ticket-perforated fs-1 d-block mb-2"></i>
            Hiện chưa có voucher phù hợp.
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.voucher-copy').forEach((button) => {
    button.addEventListener('click', async () => {
        const code = button.dataset.code || '';
        const oldHtml = button.innerHTML;

        try {
            await navigator.clipboard.writeText(code);
        } catch (error) {
            const temp = document.createElement('input');
            temp.value = code;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            temp.remove();
        }

        button.innerHTML = '<i class="bi bi-check2"></i> Đã chép';
        setTimeout(() => {
            button.innerHTML = oldHtml;
        }, 1400);
    });
});
</script>
@endpush
