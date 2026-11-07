<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public const DEFAULTS = [
        'library_name' => 'RFID Library System',
        'borrow_days' => '7',
        'max_books' => '3',
        'due_soon_days' => '2',
        'auto_close_hours' => '12',
    ];

    public function all(): array
    {
        try {
            $stored = Cache::rememberForever('library.settings', fn () => Setting::query()->pluck('value', 'key')->all());
        } catch (\Throwable $exception) {
            $stored = [];
        }

        return array_merge(self::DEFAULTS, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function int(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    public function float(string $key, float $default = 0): float
    {
        return (float) $this->get($key, $default);
    }

    public function bool(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value]
        );

        Cache::forget('library.settings');
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value === null ? null : (string) $value]
            );
        }

        Cache::forget('library.settings');
    }

    public function seedDefaults(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget('library.settings');
    }
}
