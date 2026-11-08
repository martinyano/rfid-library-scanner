<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\BookCopyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookCopyController extends Controller
{
    public function store(Request $request, Book $book, AttendanceService $attendance, BookCopyService $copies)
    {
        $request->merge([
            'rfid_uid' => $attendance->normalizeUid($request->input('rfid_uid')),
        ]);

        $data = $request->validate([
            'rfid_uid' => ['nullable', 'string', 'max:100', Rule::unique('book_copies', 'rfid_uid'), $this->notAStudentCard()],
            'status' => ['nullable', Rule::in($this->editableStatuses())],
        ]);

        $copies->add(
            $book,
            $data['rfid_uid'] ?? null,
            filled($data['status'] ?? null) ? $data['status'] : BookCopy::STATUS_AVAILABLE
        );

        return back()->with('success', 'Copy added successfully.');
    }

    public function update(Request $request, Book $book, BookCopy $copy, AttendanceService $attendance, BookCopyService $copies)
    {
        abort_unless($copy->book_id === $book->id, 404);

        if ($copy->status === BookCopy::STATUS_BORROWED) {
            return back()->with('error', 'Borrowed copies cannot be edited. Mark the loan as returned first.');
        }

        $request->merge([
            'rfid_uid' => $attendance->normalizeUid($request->input('rfid_uid')),
        ]);

        $data = $request->validate([
            'rfid_uid' => ['nullable', 'string', 'max:100', Rule::unique('book_copies', 'rfid_uid')->ignore($copy->id), $this->notAStudentCard()],
            'status' => ['required', Rule::in($this->editableStatuses())],
        ]);

        $copy->update($data);

        $copies->syncCounters($book);

        return back()->with('success', 'Copy updated successfully.');
    }

    public function destroy(Book $book, BookCopy $copy, BookCopyService $copies)
    {
        abort_unless($copy->book_id === $book->id, 404);

        try {
            $copies->remove($copy);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Copy removed successfully.');
    }

    /**
     * @return array<int, string>
     */
    private function editableStatuses(): array
    {
        return [
            BookCopy::STATUS_AVAILABLE,
            BookCopy::STATUS_LOST,
            BookCopy::STATUS_DAMAGED,
        ];
    }

    private function notAStudentCard(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $normalized = strtoupper(preg_replace('/\s+/', '', trim((string) $value)));

            if ($normalized === '') {
                return;
            }

            if (User::query()->where('rfid_uid', $normalized)->exists()) {
                $fail('This RFID UID is already assigned to a student card.');
            }
        };
    }
}
