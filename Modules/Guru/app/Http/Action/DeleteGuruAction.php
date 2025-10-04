<?php

namespace Modules\Guru\Http\Action;

use Illuminate\Support\Facades\DB;
use Modules\Guru\Models\Guru;

class DeleteGuruAction
{
    /**
     * Menghapus satu atau banyak guru beserta user terkait.
     *
     * @param int|array $ids ID guru (int) atau array ID guru.
     * @return array Data guru dan user yang dihapus.
     * @throws \Throwable
     */
    public function __invoke($ids): array
    {
        return DB::transaction(function () use ($ids) {
            $idsToDelete = is_array($ids) ? $ids : [$ids];
            $gurus = Guru::with('user')->whereIn('id', $idsToDelete)->get();

            $deletedData = [];

            foreach ($gurus as $guru) {
                $deletedItem = [
                    'guru' => $guru->toArray(),
                    'user' => $guru->user ? $guru->user->toArray() : null,
                ];

                if ($guru->user) {
                    $guru->user->delete();
                }
                $guru->delete();

                $deletedData[] = $deletedItem;
            }

            return $deletedData;
        });
    }
}
