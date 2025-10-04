<?php

namespace App\Services\UserManagement;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUserWithSourceService
{
    /**
     * Update user beserta entitas sumbernya (siswa, guru, dll).
     *
     * @param string $sourceType
     * @param string $modelClass
     * @param int $sourceId
     * @param array $data
     * @return mixed
     */
    public function __invoke(string $sourceType, string $modelClass, int $sourceId, array $data)
    {
        return DB::transaction(function () use ($sourceType, $modelClass, $sourceId, $data) {
            // Ambil entitas sumber (misal: Siswa/Guru)
            $source = $modelClass::findOrFail($sourceId);

            // Ambil user terkait
            $user = $source->user ?? User::where('source', $sourceType)->where('source_id', $sourceId)->firstOrFail();

            // Update data user
            $user->name = $data['name'] ?? ($data['nama_lengkap'] ?? $user->name);
            $user->email = $data['email'] ?? $user->email;
            if (isset($data['password']) && $data['password']) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            // Hapus password agar tidak dilempar ke entitas lainnya
            if (isset($data['password'])) {
                unset($data['password']);
            }

            // Update data entitas sumber
            $data['user_id'] = $user->id;
            $source->update($data);

            return $source->fresh();
        });
    }
}
