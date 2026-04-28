@extends('layouts.app')

@section('content')

<div style="max-width:1200px; margin:20px auto; padding:0 10px;">

    <!-- ===== HEADER SHOPEE STYLE ===== -->
    <div style="display:flex; align-items:center; gap:15px; flex-wrap:nowrap;">

        <!-- CART -->
        <div style="flex:0 0 auto; position:relative;">
            <a href="{{ route('user.cart') }}"
               style="display:flex; align-items:center; font-size:50px; text-decoration:none; color:#333; position:relative;">
                <i class="bi bi-cart-fill"></i>

                @if(!empty($totalItems) && $totalItems > 0)
                    <span style="position:absolute; top:-6px; right:-10px; background:#ff0000; color:#fff; font-size:12px; padding:2px 6px; border-radius:50%;">
                        {{ $totalItems }}
                    </span>
                @endif
            </a>
        </div>

        <!-- FLASH SALE -->
        @if($flashPercent > 0)
        <div class="flash-sale-banner"
             style="flex:1; min-width:0; text-align:center; background:linear-gradient(90deg,#ff4d4f,#ff0000); color:#fff; padding:10px 15px; border-radius:10px;">

            <div style="display:flex; justify-content:center; align-items:center; gap:10px; flex-wrap:nowrap; white-space:nowrap;">

                <div style="font-weight:bold;">
                    🔥 FLASH SALE
                    <span style="background:rgba(255,255,255,0.2); padding:2px 6px; border-radius:6px; font-size:13px; margin-left:5px;">
                        {{ $flashStart }} - {{ $flashEnd }}
                    </span>
                </div>

                <div style="font-weight:bold;">
                    -{{ round($flashPercent) }}%
                </div>

            </div>

            @if($flashnote)
                <div style="font-size:12px; margin-top:5px; opacity:0.9;">
                    {{ $flashnote }}
                </div>
            @endif

        </div>
        @endif

    </div>

    <!-- ===== SEARCH ===== -->
    <form id="searchForm" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:15px;">

        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Tìm theo tên..."
               style="flex:1; padding:10px; border:1px solid #ddd; border-radius:6px;">

        <select id="serviceSort" name="sort"
                style="padding:10px; border:1px solid #ddd; border-radius:6px;">
            <option value="name">Tên</option>
            <option value="priceAsc">Giá thấp → cao</option>
            <option value="priceDesc">Giá cao → thấp</option>
            <option value="rating">Đánh giá</option>
        </select>

        <button type="submit"
                style="padding:10px 15px; background:#007bff; color:#fff; border:none; border-radius:6px;">
            Tìm
        </button>

    </form>
</br>
    <!-- ===== PRODUCTS ===== -->
    <div id="productList" class="product-grid">

        @foreach($services as $service)

        <div class="product-card" data-price="{{ $service->price }}" data-rating="{{ $service->avg_rating ?? 0 }}">

            <a href="{{ route('user.serviceDetail', $service->id) }}">
                <img src="{{ $service->image ? asset('uploads/services/'.$service->image) : asset('assets/images/default.png') }}"
                     style="width:100%; height:160px; object-fit:cover;">
            </a>

            <div style="padding:8px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ $service->name }}
            </div>

            @if(($service->discount_percent ?? 0) > 0)
                <div style="position:absolute; top:10px; left:10px; background:#ff0000; color:#fff; font-size:12px; padding:3px 6px; border-radius:5px;">
                    -{{ round($service->discount_percent) }}%
                </div>
            @endif

            <div style="padding:5px 8px;">
                @if(($service->discount_percent ?? 0) > 0)
                    <div style="color:#e53935; font-weight:bold;">
                        {{ number_format($service->final_price,0,',','.') }} đ
                    </div>
                    <div style="text-decoration:line-through; font-size:12px; color:#999;">
                        {{ number_format($service->price,0,',','.') }} đ
                    </div>
                @else
                    <div style="font-weight:bold; color:#e53935;">
                        {{ number_format($service->price,0,',','.') }} đ
                    </div>
                @endif
            </div>

            <button class="btn-add-cart" data-service-id="{{ $service->id }}">
                +
            </button>

        </div>

        @endforeach

    </div>

</div>

<style>
.product-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
    gap:15px;
}

.product-card{
    position:relative;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
    transition:0.2s;
}

.product-card:hover{
    transform:translateY(-3px);
    box-shadow:0 6px 18px rgba(0,0,0,0.15);
}

.btn-add-cart{
    position:absolute;
    bottom:10px;
    right:10px;
    width:36px;
    height:36px;
    border-radius:50%;
    border:none;
    background:#28a745;
    color:#fff;
    font-weight:bold;
}
</style>

@endsection
 