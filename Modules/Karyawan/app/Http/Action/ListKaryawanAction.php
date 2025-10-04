<?php

namespace Modules\Karyawan\Http\Action;

use Modules\Karyawan\Models\Karyawan;

class ListKaryawanAction
{
    /**
     * Mengambil daftar karyawan dengan pagination dan pencarian berdasarkan nama.
     *
     * @param array $params Parameter pencarian dan pagination (opsional: 'search', 'per_page').
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function __invoke(array $params = [])
    {
        $query = Karyawan::query();

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where('name', 'like', '%' . $search . '%');
        }
        $query->with('user');

        $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : 15;

        return $query->paginate($perPage);
    }
}
