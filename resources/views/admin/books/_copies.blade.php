@php($statuses = ['available' => 'Available', 'lost' => 'Lost', 'damaged' => 'Damaged'])
<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-upc-scan me-1"></i> Physical copies</span>
        <span class="badge badge-soft-secondary">{{ $book->copies->count() }} registered</span>
    </div>
    <div class="card-body">
        <p class="small text-muted-library">
            Assign an RFID tag to each physical copy. At the kiosk, a student taps their card and then the book tag to borrow or return it.
        </p>

        <div class="alert alert-info d-flex align-items-start gap-2 py-2">
            <i class="bi bi-info-circle mt-1"></i>
            <div class="small">
                Copies marked available or borrowed count toward the book's total. Lost and damaged copies stay listed for records but are removed from circulation.
            </div>
        </div>

        @if ($book->copies->isEmpty())
            <div class="text-center text-muted-library py-3 small">No copies registered yet. Add one below.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-4">
                    <thead>
                        <tr>
                            <th>Copy</th>
                            <th>RFID tag</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($book->copies as $copy)
                            <tr>
                                <td class="small text-muted-library">#{{ $copy->id }}</td>
                                @if ($copy->status === 'borrowed')
                                    <td><code>{{ $copy->rfid_uid ?: 'Not assigned' }}</code></td>
                                    <td><span class="badge {{ $copy->status_badge }}">{{ $copy->status_label }}</span></td>
                                    <td class="text-end small text-muted-library">Manage via the loan</td>
                                @else
                                    <td colspan="3">
                                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                                            <form method="POST" action="{{ route('admin.books.copies.update', [$book, $copy]) }}" class="d-flex flex-wrap gap-2 align-items-center">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="rfid_uid" value="{{ old('rfid_uid', $copy->rfid_uid) }}" class="form-control form-control-sm" style="max-width: 14rem;" placeholder="Scan or type tag" maxlength="100" autocomplete="off">
                                                <select name="status" class="form-select form-select-sm" style="max-width: 9rem;">
                                                    @foreach ($statuses as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('status', $copy->status) === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.books.copies.destroy', [$book, $copy]) }}" onsubmit="return confirm('Remove this copy?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.books.copies.store', $book) }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-sm-5">
                <label for="new_copy_rfid" class="form-label small fw-semibold">New copy RFID tag</label>
                <input type="text" name="rfid_uid" id="new_copy_rfid" value="{{ old('rfid_uid') }}" class="form-control @error('rfid_uid') is-invalid @enderror" placeholder="Scan or type tag" maxlength="100" autocomplete="off">
                @error('rfid_uid')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-4">
                <label for="new_copy_status" class="form-label small fw-semibold">Status</label>
                <select name="status" id="new_copy_status" class="form-select">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'available') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i> Add copy</button>
            </div>
        </form>
    </div>
</div>
