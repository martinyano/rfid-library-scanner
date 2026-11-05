@extends('layouts.admin')

@section('title', 'Add category')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.categories.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i> Back to categories</a>
        <h1 class="admin-page-title h3 mt-2 mb-1">Add category</h1>
        <p class="text-muted-library mb-0">Categories help students filter the catalog.</p>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.categories.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Category name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required maxlength="120">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="description" class="form-label small fw-semibold">Description <span class="text-muted-library fw-normal">(optional)</span></label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" maxlength="2000">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Save category</button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-light border">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
