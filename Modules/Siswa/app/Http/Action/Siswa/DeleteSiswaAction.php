<?php

namespace Modules\Siswa\Http\Action\Siswa;

use Modules\Siswa\Models\Siswa;
use Illuminate\Support\Facades\DB;

class DeleteSiswaAction
{
    /**
     * Menghapus satu atau banyak siswa beserta user terkait.
     *
     * @param int|array $ids ID siswa (int) atau array ID siswa.
     * @return array Data siswa dan user yang dihapus.
     * @throws \Throwable
     */
    public function __invoke($ids): array
    {
        return DB::transaction(function () use ($ids) {
            $idsToDelete = is_array($ids) ? $ids : [$ids];
            $siswas = Siswa::with('user')->whereIn('id', $idsToDelete)->get();

            $deletedData = [];

            foreach ($siswas as $siswa) {
                $deletedItem = [
                    'siswa' => $siswa->toArray(),
                    'user' => $siswa->user ? $siswa->user->toArray() : null,
                ];

                if ($siswa->user) {
                    $siswa->user->delete();
                }
                $siswa->delete();

                $deletedData[] = $deletedItem;
            }

            return $deletedData;
        });
    }
}
