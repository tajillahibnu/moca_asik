<?php

namespace Modules\Siswa\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Modules\Siswa\Http\Action\Siswa\CreateSiswaAction;
use Modules\Siswa\Http\Action\Siswa\DeleteSiswaAction;
use Modules\Siswa\Http\Action\Siswa\ListSiswaAction;
use Modules\Siswa\Http\Action\Siswa\UpdateSiswaAction;
use Modules\Siswa\Models\Siswa;

class SiswaController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request, ListSiswaAction $action)
    {
        try {
            $params = [
                'search' => $request->query('search'),
                'per_page' => $request->query('per_page'),
            ];

            $siswa = $action($params);

            return $this
                ->apiResponse($siswa)
                ->setMessage('Daftar siswa berhasil diambil')
                ->addPaginationMeta($siswa)
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(int $id)
    {
        try {
            $siswa = Siswa::with('user')->findOrFail($id);
            return $this
                ->apiResponse($siswa)
                ->setMessage('Siswa berhasil ditemukan')
                ->setStatusCode(201)
                ->send();
        } catch (\Throwable $th) {
            return $this->handleException($th);
        }
    }

    public function store(Request $request, CreateSiswaAction $action)
    {
        try {
            $siswa = $action($request->all());

            return $this
                ->apiResponse($siswa)
                ->setMessage('Siswa berhasil ditambahkan')
                ->setStatusCode(201)
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, UpdateSiswaAction $action, int $id)
    {
        try {
            $siswa = $action($id, $request->all());

            return $this
                ->apiResponse($siswa)
                ->setMessage('Siswa berhasil diperbarui')
                ->setStatusCode(200)
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function destroy(int $id, DeleteSiswaAction $deleteSiswaAction)
    {
        try {
            $deletedData = $deleteSiswaAction($id);

            return $this
                ->apiResponse($deletedData)
                ->setMessage('Siswa beserta user terkait berhasil dihapus')
                ->setStatusCode(200)
                ->send();
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Menghapus banyak siswa sekaligus beserta user terkait.
     */
    public function massDestroy(Request $request, DeleteSiswaAction $deleteSiswaAction)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:siswas,id',
            ], [
                'ids.required' => 'Field ids wajib diisi.',
                'ids.array' => 'Field ids harus berupa array.',
                'ids.min' => 'Minimal satu siswa harus dipilih untuk dihapus.',
                'ids.*.integer' => 'Setiap id siswa harus berupa angka.',
                'ids.*.exists' => 'Salah satu id siswa tidak ditemukan di database.',
            ]);
            $ids = $validated['ids'];

            // Cek apakah ada siswa yang ditemukan
            $siswas = Siswa::whereIn('id', $ids)->get();
            if ($siswas->isEmpty()) {
                return $this
                    ->apiResponse(null)
                    ->setMessage('Tidak ada data siswa yang ditemukan untuk dihapus')
                    ->setStatusCode(404)
                    ->send();
            }

            $deletedData = $deleteSiswaAction($ids);

            return $this
                ->apiResponse($deletedData)
                ->setMessage('Siswa beserta user terkait berhasil dihapus secara massal')
                ->setStatusCode(200)
                ->send();
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
