<?php

namespace Modules\Guru\Http\Action;

use Modules\Guru\Models\Guru;

class ListGuruAction
{
    /**
     * Mengambil daftar guru dengan pagination dan pencarian berdasarkan nama.
     *
     * @param array $params Parameter pencarian dan pagination (optional: 'search', 'per_page').
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function __invoke(array $params = [])
    {
        $query = Guru::query();

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where('name', 'like', '%' . $search . '%');
        }
        $query->with('user');

        $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : 15;

        return $query->paginate($perPage);
    }
}
