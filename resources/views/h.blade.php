@php
$reviews = [
    (object)[
        'user' => 'nguyendinhhuy',
        'rating' => 5,
        'time' => '2023-01-17 16:35',
        'variant' => '2M',
        'comment' => 'Áo rất đẹp, hình in rất xinh và chất chắn. Giao hàng nhanh.',
        'media' => ['img1.jpg', 'img2.jpg'],
        'reply' => 'Cảm ơn bạn đã ủng hộ shop ❤️'
    ],
    (object)[
        'user' => 'thumai1612',
        'rating' => 5,
        'time' => '2025-01-17 17:07',
        'variant' => '1.2XL',
        'comment' => 'Bé trai nhà mình mặc rất vừa, mua thêm màu khác.',
        'media' => ['img3.jpg', 'img4.jpg', 'img5.jpg'],
        'reply' => 'Shop cảm ơn bạn nhiều ạ!'
    ],
    (object)[
        'user' => 's*****3',
        'rating' => 5,
        'time' => '2025-02-21 23:04',
        'variant' => '8.4XL',
        'comment' => '2 áo mặc thoải mái, rộng rãi.',
        'media' => ['img6.jpg'],
        'reply' => null
    ]
];
@endphp

<style>
.review-box{border-bottom:1px solid #eee;padding:15px 0}
.user{font-weight:600}
.meta{font-size:12px;color:#888}
.comment{margin-top:5px}
.reply{background:#f5f5f5;padding:10px;margin-top:10px;border-radius:6px;font-size:13px}
.media img{width:70px;height:70px;object-fit:cover;margin-right:5px;border-radius:4px}
.star{color:#ff5c00}
</style>

<div class="container mt-4">

    <h4>Đánh giá sản phẩm</h4>

    @foreach($reviews as $r)
        <div class="review-box">

            {{-- USER INFO --}}
            <div class="user">{{ $r->user }}</div>

            {{-- STARS --}}
            <div class="star">
                @for($i=1;$i<=5;$i++)
                    {{ $i <= $r->rating ? '★' : '☆' }}
                @endfor
            </div>

            {{-- META --}}
            <div class="meta">
                {{ $r->time }} | Phân loại: {{ $r->variant }}
            </div>

            {{-- COMMENT --}}
            <div class="comment">
                {{ $r->comment }}
            </div>

            {{-- MEDIA --}}
            @if(!empty($r->media))
                <div class="media mt-2 d-flex">
                    @foreach($r->media as $img)
                        <img src="{{ asset('uploads/demo/'.$img) }}">
                    @endforeach
                </div>
            @endif

            {{-- SELLER REPLY --}}
            @if($r->reply)
                <div class="reply">
                    <b>Phản Hồi Của Người Bán:</b><br>
                    {{ $r->reply }}
                </div>
            @endif

        </div>
    @endforeach

</div>