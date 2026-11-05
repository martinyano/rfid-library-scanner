@php($student = $student ?? null)
<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label small fw-semibold">Full name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $student?->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="120">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label small fw-semibold">Email address</label>
        <input type="email" name="email" id="email" value="{{ old('email', $student?->email) }}" class="form-control @error('email') is-invalid @enderror" required maxlength="150">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Used for due date and overdue reminders.</div>
    </div>
    <div class="col-md-6">
        <label for="student_number" class="form-label small fw-semibold">Student number <span class="text-muted-library fw-normal">(optional)</span></label>
        <input type="text" name="student_number" id="student_number" value="{{ old('student_number', $student?->student_number) }}" class="form-control @error('student_number') is-invalid @enderror" maxlength="50">
        @error('student_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="rfid_uid" class="form-label small fw-semibold">RFID card UID</label>
        <input type="text" name="rfid_uid" id="rfid_uid" value="{{ old('rfid_uid', $student?->rfid_uid) }}" class="form-control @error('rfid_uid') is-invalid @enderror" required maxlength="100" autocomplete="off" placeholder="Scan the card or type the UID">
        @error('rfid_uid')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Tap the card on the reader here, or enter the UID manually.</div>
    </div>
    <div class="col-md-6">
        <label for="course" class="form-label small fw-semibold">Course <span class="text-muted-library fw-normal">(optional)</span></label>
        <input type="text" name="course" id="course" value="{{ old('course', $student?->course) }}" class="form-control @error('course') is-invalid @enderror" maxlength="120">
        @error('course')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="year_level" class="form-label small fw-semibold">Year level <span class="text-muted-library fw-normal">(optional)</span></label>
        <input type="text" name="year_level" id="year_level" value="{{ old('year_level', $student?->year_level) }}" class="form-control @error('year_level') is-invalid @enderror" maxlength="50">
        @error('year_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label for="phone" class="form-label small fw-semibold">Phone <span class="text-muted-library fw-normal">(optional)</span></label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $student?->phone) }}" class="form-control @error('phone') is-invalid @enderror" maxlength="30">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" @checked(old('is_active', $student?->is_active ?? true))>
            <label class="form-check-label small fw-semibold" for="is_active">Account is active</label>
        </div>
        <div class="form-text">Inactive students cannot scan into the library or borrow books.</div>
    </div>
</div>
