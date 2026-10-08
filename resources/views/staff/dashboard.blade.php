@extends('layouts.admin')

@section('title', 'Staff Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-info">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Staff Dashboard</h4>
            </div>
            <div class="card-body">
                <h5 class="card-title">Welcome, {{ auth()->user()->name ?? 'Staff Member' }}!</h5>
                <p class="card-text">Manage administrative tasks, respond to user inquiries, and handle university records seamlessly.</p>
                <hr>
                <button class="btn btn-info text-white">View Support Tickets</button>
                <button class="btn btn-outline-info">Manage Records</button>
            </div>
        </div>
    </div>
</div>
@endsection
