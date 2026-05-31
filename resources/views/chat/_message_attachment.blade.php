@if($message->hasAttachment())
    @if($message->attachmentIsImage())
        <a href="{{ $message->attachmentUrl() }}" target="_blank" class="d-block mt-2">
            <img
                src="{{ $message->attachmentUrl() }}"
                alt="{{ $message->attachment_original_name }}"
                class="img-fluid rounded"
                style="max-height:220px;object-fit:contain;"
            >
        </a>
    @else
        <a
            href="{{ $message->attachmentUrl() }}"
            target="_blank"
            download="{{ $message->attachment_original_name }}"
            class="btn btn-sm btn-light text-dark border mt-2"
        >
            <i class="bi bi-paperclip"></i>
            Tai file: {{ $message->attachment_original_name }}
        </a>
    @endif
@endif
