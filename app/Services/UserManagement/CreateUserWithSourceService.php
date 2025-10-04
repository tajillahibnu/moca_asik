<?php

namespace App\Services\UserManagement;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserWithSourceService
{
    /**
     * Buat user beserta entitasnya (siswa, guru, dll).
     */
    public function __invoke(string $sourceType, string $modelClass, array $data)
    {
        return DB::transaction(function () use ($sourceType, $modelClass, $data) {
            $user = User::create([
                'name' => $data['name'] ?? ($data['nama_lengkap'] ?? 'No Name'),
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password'),
                'source' => $sourceType,
                'source_id' => null,
            ]);

            unset($data['password']);
            $data['user_id'] = $user->id;

            $source = $modelClass::create($data);

            $user->source_id = $source->id;
            $user->save();

            return $source;
        });
    }
}
