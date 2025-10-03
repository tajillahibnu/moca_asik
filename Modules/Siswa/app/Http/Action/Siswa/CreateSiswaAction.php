<?php

namespace Modules\Siswa\Http\Action\Siswa;

use Modules\Siswa\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateSiswaAction
{
    /**
     * Menambah siswa baru beserta user-nya.
     *
     * @param array $data Data siswa dan user.
     * @return Siswa
     * @throws \Throwable
     */
    public function __invoke(array $data): Siswa
    {
        return DB::transaction(function () use ($data) {
            // Buat user baru
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $data['email'],
                'password' => isset($data['password']) ? Hash::make($data['password']) : Hash::make('password'),
                'source' => 'siswa',
                'source_id' => null, // akan diupdate setelah siswa dibuat
            ]);
            if (isset($data['password'])) {
                unset($data['password']);
            }

            // Buat siswa baru
            $siswaData = $data;
            $siswaData['user_id'] = $user->id;
            $siswa = Siswa::create($siswaData);

            // Update user source_id dengan id siswa
            $user->source_id = $siswa->id;
            $user->save();

            return $siswa;
        });
    }
}
