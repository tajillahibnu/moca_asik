<?php

namespace App\Http\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

abstract class BaseCreateUserWithSourceAction
{
    /**
     * Nama tipe sumber yang akan disimpan di kolom 'source' tabel users.
     * Misalnya: 'guru', 'siswa', 'karyawan'.
     */
    public const SOURCE = 'source';

    /**
     * Magic method agar class bisa dipanggil seperti function (invoke).
     *
     * @param array $data
     * @return mixed
     * @throws \Throwable
     */
    public function __invoke(array $data)
    {
        return $this->handle($data);
    }

    /**
     * Handle pembuatan user + sumber datanya (guru/siswa/dll).
     *
     * @param array $data
     * @return mixed
     * @throws \Throwable
     */
    public function handle(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Penyesuaian: nama diambil dari 'name' atau 'nama_lengkap', default 'No Name'
            $name = $data['name'] ?? ($data['nama_lengkap'] ?? 'No Name');
            $email = $data['email'];
            $password = isset($data['password']) ? Hash::make($data['password']) : Hash::make('password');

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'source' => static::SOURCE,
                'source_id' => null,
            ]);

            // Hapus password agar tidak dilempar ke entitas lainnya
            if (isset($data['password'])) {
                unset($data['password']);
            }

            // Tambahkan user_id ke data
            $data['user_id'] = $user->id;

            // Buat entitas utama (Guru, Siswa, dll)
            $source = $this->createSource($data);

            // Update source_id di user
            $user->source_id = $source->id;
            $user->save();

            return $source;
        });
    }

    /**
     * Method ini harus diimplementasikan di subclass untuk menyimpan entitas terkait.
     *
     * @param array $data
     * @return mixed
     */
    abstract protected function createSource(array $data);
}
