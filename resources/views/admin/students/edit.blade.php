@extends('layouts.admin')

@section('title', 'Edit student')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.students.show', $student) }}" class="small"><i class="bi bi-arrow-left me-1"></i> Back to student</a>
        <h1 class="admin-page-title h3 mt-2 mb-1">Edit student</h1>
        <p class="text-muted-library mb-0">{{ $student->name }}</p>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.students.update', $student) }}">
                @csrf
                @method('PUT')
                @include('admin.students._fields', ['student' => $student])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Update student</button>
                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
