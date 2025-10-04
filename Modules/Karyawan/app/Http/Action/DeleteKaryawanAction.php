<?php

namespace Modules\Karyawan\Http\Action;

use Illuminate\Support\Facades\DB;
use Modules\Karyawan\Models\Karyawan;

class DeleteKaryawanAction
{
    /**
     * Menghapus satu atau banyak karyawan beserta user terkait.
     *
     * @param int|array $ids ID karyawan (int) atau array ID karyawan.
     * @return array Data karyawan dan user yang dihapus.
     * @throws \Throwable
     */
    public function __invoke($ids): array
    {
        return DB::transaction(function () use ($ids) {
            $idsToDelete = is_array($ids) ? $ids : [$ids];
            $karyawans = Karyawan::with('user')->whereIn('id', $idsToDelete)->get();

            $deletedData = [];

            foreach ($karyawans as $karyawan) {
                $deletedItem = [
                    'karyawan' => $karyawan->toArray(),
                    'user' => $karyawan->user ? $karyawan->user->toArray() : null,
                ];

                if ($karyawan->user) {
                    $karyawan->user->delete();
                }
                $karyawan->delete();

                $deletedData[] = $deletedItem;
            }

            return $deletedData;
        });
    }
}
