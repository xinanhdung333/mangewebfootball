@extends('layouts.app')

@section('content')
<table class="table table-striped">

    <thead>
        <tr>
            <th>User</th>
            <th>Action</th>
            <th>Mô tả</th>
            <th>IP</th>
            <th>Thời gian</th>
        </tr>
    </thead>

    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>{{ $log->user_id ?? 'System' }}</td>
            <td>{{ $log->action }}</td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->ip_address }}</td>
            <td>{{ $log->created_at }}</td>
        </tr>
        @endforeach
    </tbody>

</table>

{{ $logs->links() }}

@endsection