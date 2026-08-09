<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class CandidateBackup
{
    private const FILE_PATH = 'recruitment/candidate_backup.json';

    public static function all(): array
    {
        if (!Storage::disk('local')->exists(self::FILE_PATH)) {
            return [];
        }

        $raw = Storage::disk('local')->get(self::FILE_PATH);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function upsert(string $userId, array $payload): void
    {
        $records = self::all();
        $records[$userId] = $payload;
        self::persist($records);
    }

    public static function remove(string $userId): void
    {
        $records = self::all();

        if (!array_key_exists($userId, $records)) {
            return;
        }

        unset($records[$userId]);
        self::persist($records);
    }

    private static function persist(array $records): void
    {
        Storage::disk('local')->put(
            self::FILE_PATH,
            json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
