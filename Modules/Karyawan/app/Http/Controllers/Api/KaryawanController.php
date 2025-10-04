<?php

namespace Modules\Karyawan\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserManagement\CreateUserWithSourceService;
use App\Services\UserManagement\UpdateUserWithSourceService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Karyawan\Models\Karyawan;
use Modules\Karyawan\Http\Action\DeleteKaryawanAction;
use Modules\Karyawan\Http\Action\ListKaryawanAction;

class KaryawanController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ListKaryawanAction $action)
    {
        try {
            $params = [
                'search' => $request->query('search'),
                'per_page' => $request->query('per_page'),
            ];

            $karyawans = $action($params);

            return $this
                ->apiResponse($karyawans)
                ->setMessage('Daftar karyawan berhasil diambil')
                ->addPaginationMeta($karyawans)
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreateUserWithSourceService $service)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'nip' => 'nullable|string|max:255|unique:karyawans,nip',
                'email' => 'required|email|unique:users,email|unique:karyawans,email',
                'no_hp' => 'nullable|string|max:20',
                'jenis_kelamin' => 'nullable|string|max:10',
                'tempat_lahir' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string|max:255',
                'jabatan' => 'nullable|string|max:255',
                'is_aktif' => 'nullable|boolean',
                'password' => 'required|string',
            ]);

            $validated['created_by'] = Auth::id();

            $karyawan = $service('karyawan', Karyawan::class, $validated);
            $karyawan->load('user');

            // Assign role karyawan ke user jika ada spatie/permission
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $karyawan->user->assignRole('karyawan');
            }

            return $this
                ->apiResponse($karyawan)
                ->setMessage('Karyawan berhasil ditambahkan')
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
            $karyawan = Karyawan::with('user')->findOrFail($id);
            return $this
                ->apiResponse($karyawan)
                ->setMessage('Karyawan berhasil ditemukan')
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
        $karyawan = Karyawan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'nip' => 'nullable|string|max:255|unique:karyawans,nip,' . $karyawan->id,
            'email' => 'sometimes|required|email|unique:karyawans,email,' . $karyawan->id . '|unique:users,email,' . $karyawan->user_id,
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|string|max:10',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'is_aktif' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $validated['updated_by'] = Auth::id();

        try {
            $updatedKaryawan = $service('karyawan', Karyawan::class, $id, $validated);

            return $this
                ->apiResponse($updatedKaryawan->load('user'))
                ->setMessage('Karyawan berhasil diupdate')
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id, DeleteKaryawanAction $deleteKaryawanAction)
    {
        try {
            $deletedData = $deleteKaryawanAction($id);

            return $this
                ->apiResponse($deletedData)
                ->setMessage('Karyawan beserta user terkait berhasil dihapus')
                ->setStatusCode(200)
                ->send();
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Menghapus banyak karyawan sekaligus beserta user terkait.
     */
    public function massDestroy(Request $request, DeleteKaryawanAction $deleteKaryawanAction)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:karyawans,id',
            ], [
                'ids.required' => 'Field ids wajib diisi.',
                'ids.array' => 'Field ids harus berupa array.',
                'ids.min' => 'Minimal satu karyawan harus dipilih untuk dihapus.',
                'ids.*.integer' => 'Setiap id karyawan harus berupa angka.',
                'ids.*.exists' => 'Salah satu id karyawan tidak ditemukan di database.',
            ]);
            $ids = $validated['ids'];

            $karyawans = Karyawan::whereIn('id', $ids)->get();
            if ($karyawans->isEmpty()) {
                return $this
                    ->apiResponse(null)
                    ->setMessage('Tidak ada data karyawan yang ditemukan untuk dihapus')
                    ->setStatusCode(404)
                    ->send();
            }

            $deletedData = $deleteKaryawanAction($ids);

            return $this
                ->apiResponse($deletedData)
                ->setMessage('Karyawan beserta user terkait berhasil dihapus secara massal')
                ->setStatusCode(200)
                ->send();
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
