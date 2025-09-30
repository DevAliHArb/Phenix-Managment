@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Employee Vacation Details</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Employee:</strong> {{ $item->employee->first_name ?? '' }} {{ $item->employee->last_name ?? '' }}</p>
            <p><strong>Date:</strong> {{ $item->date }}</p>
            <p><strong>Reason:</strong> {{ $item->reason }}</p>
            <p><strong>Type:</strong> {{ $item->type->name ?? '' }}</p>
            <p><strong>Attachment:</strong>
                @if($item->attachment)
                    <a href="{{ asset('attachments/'.$item->attachment) }}" target="_blank">View Attachment</a>
                @else
                    None
                @endif
            </p>
            <a href="{{ route('employee-vacations.edit', $item->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('employee-vacations.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
