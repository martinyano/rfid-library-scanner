<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\FineService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = User::query()
            ->students()
            ->withCount(['borrowings as active_loans_count' => fn ($query) => $query->where('status', 'borrowed')])
            ->when($request->string('q')->trim()->toString(), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('student_number', 'like', "%{$q}%")
                        ->orWhere('rfid_uid', 'like', "%{$q}%");
                });
            })
            ->when($request->input('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request, AttendanceService $attendance)
    {
        $request->merge([
            'rfid_uid' => $attendance->normalizeUid($request->input('rfid_uid')),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'student_number' => ['nullable', 'string', 'max:50', 'unique:users,student_number'],
            'rfid_uid' => ['required', 'string', 'max:100', 'unique:users,rfid_uid'],
            'course' => ['nullable', 'string', 'max:120'],
            'year_level' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['role'] = 'student';
        $data['password'] = null;
        $data['is_active'] = $request->boolean('is_active');

        User::query()->create($data);

        return redirect()->route('admin.students.index')->with('success', 'Student registered successfully.');
    }

    public function show(User $student, FineService $fines)
    {
        $student->load([
            'borrowings' => fn ($query) => $query->with('book')->latest('borrowed_at'),
            'fines' => fn ($query) => $query->with('borrowing.book')->latest(),
            'attendanceLogs' => fn ($query) => $query->latest('entry_at')->limit(20),
        ]);

        return view('admin.students.show', [
            'student' => $student,
            'activeLoans' => $student->borrowings->where('status', 'borrowed'),
            'unpaidFines' => $fines->unpaidTotalFor($student),
        ]);
    }

    public function edit(User $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, User $student, AttendanceService $attendance)
    {
        $request->merge([
            'rfid_uid' => $attendance->normalizeUid($request->input('rfid_uid')),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($student->id)],
            'student_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'student_number')->ignore($student->id)],
            'rfid_uid' => ['required', 'string', 'max:100', Rule::unique('users', 'rfid_uid')->ignore($student->id)],
            'course' => ['nullable', 'string', 'max:120'],
            'year_level' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $student->update($data);

        return redirect()->route('admin.students.show', $student)->with('success', 'Student details updated successfully.');
    }

    public function destroy(User $student)
    {
        $hasRecords = $student->borrowings()->exists()
            || $student->fines()->exists()
            || $student->attendanceLogs()->exists();

        if ($hasRecords) {
            return back()->with('error', 'This student has library records and cannot be deleted. Deactivate the account instead.');
        }

        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }
}
