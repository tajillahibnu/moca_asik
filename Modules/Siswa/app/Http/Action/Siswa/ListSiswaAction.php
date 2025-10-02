<?php

namespace Modules\Siswa\Http\Action\Siswa;

use Modules\Siswa\Models\Siswa;

class ListSiswaAction
{
    /**
     * Mengambil daftar siswa dengan pagination dan pencarian berdasarkan nama.
     *
     * @param array $params Parameter pencarian dan pagination (optional: 'search', 'per_page').
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function __invoke(array $params = [])
    {
        $query = Siswa::query();

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where('nama_lengkap', 'like', '%' . $search . '%');
        }
        $query->with('user');

        $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : 15;

        return $query->paginate($perPage);
    }
}
