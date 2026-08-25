@extends('layouts.app')

@section('content')
<h2>Chatbot Manager</h2>

<form method="GET"
action="{{ route('admin.chatbot.index') }}"
class="mb-3 d-flex gap-2">

<input
type="text"
name="search"
class="form-control"
placeholder="Tìm intent..."
value="{{ request('search') }}"
>

<button class="btn btn-primary">
Tìm
</button>

<a href="{{ route('admin.chatbot.index') }}"
class="btn btn-secondary">
Reset
</a>

</form>
<div class="container">


{{-- ADD INTENT --}}
<form method="POST" action="{{ route('admin.chatbot.intent.store') }}">
@csrf

<input name="name" placeholder="Intent name">
<button>Add Intent</button>

</form>

<hr>

@foreach($intents as $intent)

<div class="card mb-3">
<div class="card-body">

{{-- EDIT INTENT --}}
<form method="POST" action="{{ route('admin.chatbot.intent.update',$intent->id) }}">
@csrf
@method('PUT')

<input name="name" value="{{ $intent->name }}">
<button class="btn btn-warning btn-sm">Update</button>

</form>

{{-- DELETE INTENT --}}
<form method="POST"
action="{{ route('admin.chatbot.intent.delete',$intent->id) }}"
style="display:inline">

@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm">
Delete Intent
</button>

</form>


<hr>

{{-- ADD KEYWORD --}}
<form method="POST" action="{{ route('admin.chatbot.keyword.store') }}">
@csrf

<input type="hidden" name="intent_id"
value="{{ $intent->id }}">

<input name="keyword"
placeholder="keyword">

<button>Add keyword</button>

</form>


{{-- ADD RESPONSE --}}
<form method="POST" action="{{ route('admin.chatbot.response.store') }}">
@csrf

<input type="hidden"
name="intent_id"
value="{{ $intent->id }}">

<input name="response"
placeholder="response">

<button>Add response</button>

</form>


<hr>

<h6>Keywords</h6>

<ul>

@foreach($intent->keywords as $k)

<li>

<form method="POST"
action="{{ route('admin.chatbot.keyword.update',$k->id) }}">

@csrf
@method('PUT')

<input name="keyword"
value="{{ $k->keyword }}">

<button class="btn btn-warning btn-sm">
Update
</button>

</form>


<form method="POST"
action="{{ route('admin.chatbot.keyword.delete',$k->id) }}"
style="display:inline">

@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm">
Delete
</button>

</form>

</li>

@endforeach

</ul>


<h6>Responses</h6>

<ul>

@foreach($intent->responses as $r)

<li>

<form method="POST"
action="{{ route('admin.chatbot.response.update',$r->id) }}">

@csrf
@method('PUT')

<input name="response"
value="{{ $r->response_text }}">

<button class="btn btn-warning btn-sm">
Update
</button>

</form>


<form method="POST"
action="{{ route('admin.chatbot.response.delete',$r->id) }}"
style="display:inline">

@csrf
@method('DELETE')

<button class="btn btn-danger btn-sm">
Delete
</button>

</form>

</li>

@endforeach

</ul>


</div>
</div>

@endforeach

<div class="d-flex justify-content-center">
{{ $intents->links() }}
</div>
{{-- GENERATE JSON --}}
<form method="POST"
action="{{ route('admin.chatbot.generate') }}">

@csrf

<button class="btn btn-success">
Generate chatbot_rules.json
</button>

</form>

<div class="mt-3">
<h5>Generated content</h5>
@if($generatedRules)
<pre class="bg-light border rounded p-3" style="max-height:400px; overflow:auto;"><code>{{ $generatedRules }}</code></pre>
@else
<p class="text-muted">Chưa có chatbot_rules.json. Hãy bấm Generate trước.</p>
@endif
</div>


</div>
</br>
</br>
</br>

@endsection