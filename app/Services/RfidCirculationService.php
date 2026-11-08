<?php

namespace App\Services;

use App\Exceptions\CirculationException;
use App\Models\BookCopy;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class RfidCirculationService
{
    public function __construct(
        private readonly AttendanceService $attendance,
        private readonly BorrowingService $borrowings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function scan(?string $uid, ?string $deviceId = null, ?string $ip = null, ?string $userAgent = null): array
    {
        $normalized = $this->attendance->normalizeUid($uid);

        if ($normalized === '') {
            throw new CirculationException('RFID UID is required.', 422);
        }

        $student = $this->attendance->findStudentByRfid($normalized);

        if ($student) {
            return $this->identifyStudent($student, $deviceId, $ip, $userAgent);
        }

        $copy = BookCopy::query()
            ->with(['book.author'])
            ->where('rfid_uid', $normalized)
            ->first();

        if (! $copy) {
            throw new CirculationException('Card or book tag is not registered.', 404);
        }

        return $this->circulateBook($copy, $deviceId, $ip);
    }

    /**
     * @return array<string, mixed>
     */
    private function identifyStudent(User $student, ?string $deviceId, ?string $ip, ?string $userAgent): array
    {
        if (! $student->is_active) {
            throw new CirculationException('Student account is inactive.', 403);
        }

        $this->attendance->entry($student, 'kiosk', $ip, $userAgent);

        Cache::put(
            $this->cacheKey($deviceId, $ip),
            $student->id,
            now()->addSeconds($this->sessionSeconds())
        );

        return [
            'status' => 'ok',
            'action' => 'identify',
            'next' => 'tap_book',
            'message' => 'Welcome, '.$student->name.'. Tap the book tag to borrow or return.',
            'attendance' => 'entry',
            'student' => $this->studentPayload($student),
            'book' => null,
            'due_at' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function circulateBook(BookCopy $copy, ?string $deviceId, ?string $ip): array
    {
        $key = $this->cacheKey($deviceId, $ip);
        $studentId = Cache::get($key);

        if (! $studentId) {
            throw new CirculationException('Tap your student card first, then the book tag.', 409);
        }

        $student = User::query()
            ->students()
            ->whereKey($studentId)
            ->first();

        if (! $student) {
            Cache::forget($key);

            throw new CirculationException('Please tap your student card again.', 409);
        }

        if (! $student->is_active) {
            Cache::forget($key);

            throw new CirculationException('Student account is inactive.', 403);
        }

        $activeLoan = Borrowing::query()
            ->where('user_id', $student->id)
            ->where('book_copy_id', $copy->id)
            ->where('status', 'borrowed')
            ->first();

        if ($activeLoan) {
            $this->borrowings->markReturned($activeLoan);

            Cache::forget($key);

            return [
                'status' => 'ok',
                'action' => 'return',
                'next' => null,
                'message' => 'Returned "'.$copy->book?->title.'" for '.$student->name.'.',
                'attendance' => 'entry',
                'student' => $this->studentPayload($student),
                'book' => $this->bookPayload($copy),
                'due_at' => null,
            ];
        }

        try {
            $borrowing = $this->borrowings->borrowCopy($student, $copy);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?? 'Unable to borrow this book.';

            throw new CirculationException($message, 409);
        }

        Cache::forget($key);

        return [
            'status' => 'ok',
            'action' => 'borrow',
            'next' => null,
            'message' => 'Borrowed "'.$copy->book?->title.'" for '.$student->name.'. Due '.$borrowing->due_at->format('M d, Y').'.',
            'attendance' => 'entry',
            'student' => $this->studentPayload($student),
            'book' => $this->bookPayload($copy),
            'due_at' => $borrowing->due_at->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function studentPayload(User $student): array
    {
        return [
            'name' => $student->name,
            'student_number' => $student->student_number,
            'course' => $student->course,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bookPayload(BookCopy $copy): array
    {
        return [
            'copy_id' => $copy->id,
            'rfid_uid' => $copy->rfid_uid,
            'title' => $copy->book?->title,
            'author' => $copy->book?->author?->name,
            'shelf_location' => $copy->book?->shelf_location,
        ];
    }

    private function cacheKey(?string $deviceId, ?string $ip): string
    {
        $scope = filled($deviceId) ? 'device:'.$deviceId : 'ip:'.$ip;

        return 'rfid.pending.'.hash('sha256', $scope);
    }

    private function sessionSeconds(): int
    {
        return max(30, (int) config('library.kiosk_session_seconds', 120));
    }
}
