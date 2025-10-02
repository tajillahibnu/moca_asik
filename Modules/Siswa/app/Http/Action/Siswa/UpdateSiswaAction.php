<?php

namespace Modules\Siswa\Http\Action\Siswa;

use Modules\Siswa\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateSiswaAction
{
    /**
     * Memperbarui data siswa beserta user-nya.
     *
     * @param int $id ID siswa yang akan diupdate.
     * @param array $data Data siswa dan user yang akan diupdate.
     * @return Siswa
     * @throws \Throwable
     */
    public function __invoke(int $id, array $data): Siswa
    {
        return DB::transaction(function () use ($id, $data) {
            $siswa = Siswa::findOrFail($id);

            // Update data siswa
            $siswa->update($data);

            // Update data user terkait jika ada perubahan
            if (isset($data['nama_lengkap']) || isset($data['email']) || isset($data['password'])) {
                $user = $siswa->user;
                if ($user) {
                    if (isset($data['nama_lengkap'])) {
                        $user->name = $data['nama_lengkap'];
                    }
                    if (isset($data['email'])) {
                        $user->email = $data['email'];
                    }
                    if (isset($data['password'])) {
                        $user->password = Hash::make($data['password']);
                    }
                    $user->save();
                }
            }

            return $siswa->fresh('user');
        });
    }
}
