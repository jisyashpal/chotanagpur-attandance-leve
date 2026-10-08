@extends('layouts.admin')

@section('title', 'Faculty Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-warning">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Faculty Dashboard</h4>
            </div>
            <div class="card-body">
                <h5 class="card-title">Welcome, Professor {{ auth()->user()->name ?? 'Faculty Member' }}!</h5>
                <p class="card-text">Manage your classes, record daily attendance, and submit student grades using the tools below.</p>
                <hr>
                <button class="btn btn-warning text-dark">Take Attendance</button>
                <button class="btn btn-outline-warning text-dark">Submit Grades</button>
            </div>
        </div>
    </div>
</div>
@endsection
