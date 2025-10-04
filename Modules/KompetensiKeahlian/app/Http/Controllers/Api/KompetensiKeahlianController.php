<?php

namespace Modules\KompetensiKeahlian\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\KompetensiKeahlian\Models\KomptAhli;
use Modules\Guru\Models\Guru;

class KompetensiKeahlianController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     * GET /api/kompetensi-keahlian
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 15);
            $data = KomptAhli::with('kepalaJurusan')->paginate($perPage);

            return $this
                ->apiResponse($data)
                ->setMessage('Data kompetensi keahlian berhasil diambil')
                ->addPaginationMeta($data)
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e, 'Gagal mengambil data kompetensi keahlian');
        }
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/kompetensi-keahlian
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'kode' => 'nullable|string|max:50',
                'deskripsi' => 'nullable|string',
                'kepala_jurusan_id' => 'nullable|exists:gurus,id',
                'is_aktif' => 'boolean',
            ]);

            $kompetensi = KomptAhli::create([
                'public_url_code'   => Str::uuid(),
                'nama'              => $validated['nama'],
                'kode'              => $validated['kode'] ?? null,
                'slug'              => Str::slug($validated['nama']),
                'deskripsi'         => $validated['deskripsi'] ?? null,
                'kepala_jurusan_id' => $validated['kepala_jurusan_id'] ?? null,
                'is_aktif'          => $validated['is_aktif'] ?? true,
                'created_by'        => Auth::id() ?? null,
            ]);

            return $this
                ->apiResponse($kompetensi)
                ->setMessage('Kompetensi keahlian berhasil ditambahkan')
                ->setStatusCode(201)
                ->send();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Gagal menambah kompetensi keahlian');
        }
    }

    /**
     * Display the specified resource.
     * GET /api/kompetensi-keahlian/{id}
     */
    public function show($id)
    {
        try {
            $kompetensi = KomptAhli::with('kepalaJurusan')->find($id);

            if (!$kompetensi) {
                return $this
                    ->apiResponse(null)
                    ->setMessage('Kompetensi keahlian tidak ditemukan.')
                    ->setStatusCode(404)
                    ->send();
            }

            return $this
                ->apiResponse($kompetensi)
                ->setMessage('Data kompetensi keahlian berhasil diambil')
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e, 'Gagal mengambil data kompetensi keahlian');
        }
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/kompetensi-keahlian/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $kompetensi = KomptAhli::find($id);

            if (!$kompetensi) {
                return $this
                    ->apiResponse(null)
                    ->setMessage('Kompetensi keahlian tidak ditemukan.')
                    ->setStatusCode(404)
                    ->send();
            }

            $validated = $request->validate([
                'nama' => 'sometimes|required|string|max:255',
                'kode' => 'nullable|string|max:50',
                'deskripsi' => 'nullable|string',
                'kepala_jurusan_id' => 'nullable|exists:gurus,id',
                'is_aktif' => 'boolean',
            ]);

            if (isset($validated['nama'])) {
                $kompetensi->nama = $validated['nama'];
                $kompetensi->slug = Str::slug($validated['nama']);
            }
            if (array_key_exists('kode', $validated)) {
                $kompetensi->kode = $validated['kode'];
            }
            if (array_key_exists('deskripsi', $validated)) {
                $kompetensi->deskripsi = $validated['deskripsi'];
            }
            if (array_key_exists('kepala_jurusan_id', $validated)) {
                $kompetensi->kepala_jurusan_id = $validated['kepala_jurusan_id'];
            }
            if (array_key_exists('is_aktif', $validated)) {
                $kompetensi->is_aktif = $validated['is_aktif'];
            }
            $kompetensi->updated_by = Auth::id() ?? null;
            $kompetensi->save();

            return $this
                ->apiResponse($kompetensi)
                ->setMessage('Kompetensi keahlian berhasil diperbarui')
                ->send();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleException($e);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Gagal memperbarui kompetensi keahlian');
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/kompetensi-keahlian/{id}
     */
    public function destroy($id)
    {
        try {
            $kompetensi = KomptAhli::find($id);

            if (!$kompetensi) {
                return $this
                    ->apiResponse(null)
                    ->setMessage('Kompetensi keahlian tidak ditemukan.')
                    ->setStatusCode(404)
                    ->send();
            }

            $kompetensi->deleted_by = Auth::id() ?? null;
            $kompetensi->save();
            $kompetensi->delete();

            return $this
                ->apiResponse(null)
                ->setMessage('Kompetensi keahlian berhasil dihapus.')
                ->send();
        } catch (\Exception $e) {
            return $this->handleException($e, 'Gagal menghapus kompetensi keahlian');
        }
    }
}
