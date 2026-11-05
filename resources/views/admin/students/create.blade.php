@extends('layouts.admin')

@section('title', 'Register student')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.students.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i> Back to students</a>
        <h1 class="admin-page-title h3 mt-2 mb-1">Register student</h1>
        <p class="text-muted-library mb-0">The RFID card UID is used for passwordless student login.</p>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.students.store') }}">
                @csrf
                @include('admin.students._fields', ['student' => null])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-person-check me-1"></i> Register student</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
