<?php

namespace Modules\Guru\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserManagement\CreateUserWithSourceService;
use App\Services\UserManagement\UpdateUserWithSourceService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Guru\Http\Action\CreateGuruAction;
use Modules\Guru\Http\Action\DeleteGuruAction;
use Modules\Guru\Http\Action\ListGuruAction;
use Modules\Guru\Http\Action\UpdateGuruAction;
use Modules\Guru\Models\Guru;

class GuruController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ListGuruAction $action)
    {
        try {
            $params = [
                'search' => $request->query('search'),
                'per_page' => $request->query('per_page'),
            ];

            $gurus = $action($params);

            return $this
                ->apiResponse($gurus)
                ->setMessage('Daftar guru berhasil diambil')
                ->addPaginationMeta($gurus)
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,CreateUserWithSourceService $service)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'nip' => 'nullable|string|max:255|unique:gurus,nip',
                'nuptk' => 'nullable|string|max:255|unique:gurus,nuptk',
                'email' => 'required|email|unique:users,email|unique:gurus,email',
                'no_hp' => 'nullable|string|max:20',
                'jenis_kelamin' => 'nullable|string|max:10',
                'tempat_lahir' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string|max:255',
                'foto' => 'nullable|string|max:255',
                'jabatan' => 'nullable|string|max:255',
                'is_aktif' => 'nullable|boolean',
                // 'password' => 'required|string|min:6|confirmed',
                'password' => 'required|string',
            ]);

            // Tambahkan created_by jika user login
            $validated['created_by'] = Auth::id();

            $guru = $service('guru', \Modules\Guru\Models\Guru::class, $validated);
            $guru->load('user');

            // Assign role guru ke user jika ada spatie/permission
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $guru->user->assignRole('guru');
            }

            return $this
                ->apiResponse($guru)
                ->setMessage('Guru berhasil ditambahkan')
                ->setStatusCode(201)
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(int $id)
    {
        try {
            $siswa = Guru::with('user')->findOrFail($id);
            return $this
                ->apiResponse($siswa)
                ->setMessage('Siswa berhasil ditemukan')
                ->setStatusCode(201)
                ->send();
        } catch (\Throwable $th) {
            return $this->handleException($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, UpdateUserWithSourceService $service)
    {
        // Ambil guru untuk validasi unique
        $guru = \Modules\Guru\Models\Guru::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'nip' => 'nullable|string|max:255|unique:gurus,nip,' . $guru->id,
            'nuptk' => 'nullable|string|max:255|unique:gurus,nuptk,' . $guru->id,
            'email' => 'sometimes|required|email|unique:gurus,email,' . $guru->id . '|unique:users,email,' . $guru->user_id,
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|string|max:10',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string|max:255',
            'foto' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'is_aktif' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        // Tambahkan updated_by jika user login
        $validated['updated_by'] = Auth::id();

        try {
            // Jalankan service untuk update guru beserta user
            $updatedGuru = $service('guru', Guru::class, $id, $validated);

            return $this
                ->apiResponse($updatedGuru->load('user'))
                ->setMessage('Guru berhasil diupdate')
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(int $id, DeleteGuruAction $deleteGuruAction)
    {
        try {
            $deletedData = $deleteGuruAction($id);

            return $this
                ->apiResponse($deletedData)
                ->setMessage('Guru beserta user terkait berhasil dihapus')
                ->setStatusCode(200)
                ->send();
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Menghapus banyak guru sekaligus beserta user terkait.
     */
    public function massDestroy(Request $request, DeleteGuruAction $deleteGuruAction)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:gurus,id',
            ], [
                'ids.required' => 'Field ids wajib diisi.',
                'ids.array' => 'Field ids harus berupa array.',
                'ids.min' => 'Minimal satu guru harus dipilih untuk dihapus.',
                'ids.*.integer' => 'Setiap id guru harus berupa angka.',
                'ids.*.exists' => 'Salah satu id guru tidak ditemukan di database.',
            ]);
            $ids = $validated['ids'];

            // Cek apakah ada guru yang ditemukan
            $gurus = Guru::whereIn('id', $ids)->get();
            if ($gurus->isEmpty()) {
                return $this
                    ->apiResponse(null)
                    ->setMessage('Tidak ada data guru yang ditemukan untuk dihapus')
                    ->setStatusCode(404)
                    ->send();
            }

            $deletedData = $deleteGuruAction($ids);

            return $this
                ->apiResponse($deletedData)
                ->setMessage('Guru beserta user terkait berhasil dihapus secara massal')
                ->setStatusCode(200)
                ->send();
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
