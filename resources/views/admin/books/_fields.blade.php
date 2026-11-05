@php($book = $book ?? null)
<div class="row g-3">
    <div class="col-12">
        <label for="title" class="form-label small fw-semibold">Title</label>
        <input type="text" name="title" id="title" value="{{ old('title', $book?->title) }}" class="form-control @error('title') is-invalid @enderror" required maxlength="255">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="author_id" class="form-label small fw-semibold">Author</label>
        <select name="author_id" id="author_id" class="form-select @error('author_id') is-invalid @enderror" required>
            <option value="">Select an author</option>
            @foreach ($authors as $author)
                <option value="{{ $author->id }}" @selected((string) old('author_id', $book?->author_id) === (string) $author->id)>{{ $author->name }}</option>
            @endforeach
        </select>
        @error('author_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="category_id" class="form-label small fw-semibold">Category</label>
        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Select a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $book?->category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="isbn" class="form-label small fw-semibold">ISBN <span class="text-muted-library fw-normal">(optional)</span></label>
        <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book?->isbn) }}" class="form-control @error('isbn') is-invalid @enderror" maxlength="50">
        @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="published_year" class="form-label small fw-semibold">Published year <span class="text-muted-library fw-normal">(optional)</span></label>
        <input type="number" name="published_year" id="published_year" value="{{ old('published_year', $book?->published_year) }}" class="form-control @error('published_year') is-invalid @enderror" min="1000" max="{{ date('Y') + 1 }}">
        @error('published_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="shelf_location" class="form-label small fw-semibold">Shelf location <span class="text-muted-library fw-normal">(optional)</span></label>
        <input type="text" name="shelf_location" id="shelf_location" value="{{ old('shelf_location', $book?->shelf_location) }}" class="form-control @error('shelf_location') is-invalid @enderror" maxlength="80" placeholder="e.g. A-03">
        @error('shelf_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="total_copies" class="form-label small fw-semibold">Total copies</label>
        <input type="number" name="total_copies" id="total_copies" value="{{ old('total_copies', $book?->total_copies ?? 1) }}" class="form-control @error('total_copies') is-invalid @enderror" required min="1" max="999">
        @error('total_copies')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label small fw-semibold">Description <span class="text-muted-library fw-normal">(optional)</span></label>
        <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" maxlength="5000">{{ old('description', $book?->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="cover_image" class="form-label small fw-semibold">Cover image <span class="text-muted-library fw-normal">(JPG, PNG, or WebP, max 2MB)</span></label>
        <input type="file" name="cover_image" id="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
        @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror

        @if ($book?->cover_url)
            <div class="d-flex align-items-center gap-3 mt-3">
                <img src="{{ $book->cover_url }}" alt="Current cover" style="width: 4.5rem; height: 6rem; object-fit: cover; border-radius: 0.5rem; border: 1px solid var(--library-border);">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remove_cover" id="remove_cover" value="1" @checked(old('remove_cover'))>
                    <label class="form-check-label small" for="remove_cover">Remove current cover</label>
                </div>
            </div>
        @endif
    </div>
</div>
