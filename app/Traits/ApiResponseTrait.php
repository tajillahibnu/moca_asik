<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait ApiResponseTrait
{
    protected array $response = [];
    protected int $statusCode = 200;
    protected array $meta = [];

    // Pesan default untuk sukses dan error
    protected string $defaultSuccessMessage = 'Request berhasil diproses';
    protected string $defaultErrorMessage = 'Terjadi kesalahan pada server';

    /**
     * Atur data awal untuk API response.
     */
    public function apiResponse($data = null): self
    {
        $this->resetResponse(); // Reset response sebelumnya

        // Jika data adalah array, ekstrak statusCode dan message jika ada
        if (is_array($data)) {
            if (isset($data['statusCode'])) {
                $this->statusCode = $data['statusCode'];
                unset($data['statusCode']);
            }

            if (isset($data['message'])) {
                $this->response['message'] = $data['message'];
                unset($data['message']);
            }
        }

        // Jika tidak ada pesan, set pesan default berdasarkan statusCode
        if (!isset($this->response['message'])) {
            $this->response['message'] = $this->isSuccessfulStatusCode($this->statusCode)
                ? $this->defaultSuccessMessage
                : $this->defaultErrorMessage;
        }

        // Atur data ke response
        $this->response['data'] = $this->sanitizeResponseData($data);

        // Set success dan status berdasarkan statusCode
        $this->response['success'] = $this->isSuccessfulStatusCode($this->statusCode);
        $this->response['status'] = $this->response['success'] ? 'success' : 'error';

        return $this;
    }

    /**
     * Reset response untuk penggunaan berulang
     */
    protected function resetResponse(): void
    {
        $this->response = [];
        $this->statusCode = 200;
        $this->meta = [];
    }

    /**
     * Cek jika status code termasuk successful (2xx)
     */
    protected function isSuccessfulStatusCode(int $code): bool
    {
        return $code >= 200 && $code < 300;
    }

    /**
     * Tambahkan pesan ke respons.
     */
    public function setMessage(string $message): self
    {
        $this->response['message'] = $message;
        return $this;
    }

    /**
     * Tambahkan meta data ke response.
     */
    public function addMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Tambahkan pagination meta data.
     */
    public function addPaginationMeta($paginatedData): self
    {
        return $this->addMeta([
            'pagination' => [
                'total' => $paginatedData->total(),
                'per_page' => $paginatedData->perPage(),
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'from' => $paginatedData->firstItem(),
                'to' => $paginatedData->lastItem(),
            ]
        ]);
    }

    /**
     * Tambahkan detail error jika ada.
     */
    public function withErrors($errors): self
    {
        $this->response['errors'] = $errors;
        $this->response['success'] = false;
        $this->response['status'] = 'error';
        return $this;
    }

    /**
     * Atur status kode HTTP.
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;

        // Update success dan status berdasarkan kode
        $this->response['success'] = $this->isSuccessfulStatusCode($code);
        $this->response['status'] = $this->response['success'] ? 'success' : 'error';

        return $this;
    }

    /**
     * Kirim respons sebagai JSON.
     */
    public function send(): JsonResponse
    {
        // Tambahkan meta data jika ada
        if (!empty($this->meta)) {
            $this->response['meta'] = $this->meta;
        }

        $response = response()->json($this->response, $this->statusCode);
        
        $this->resetResponse(); // Reset untuk penggunaan berikutnya
        return $response;
    }

    /**
     * Helper method untuk response sukses cepat.
     */
    public function success($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return $this->apiResponse($data)
            ->setMessage($message ?? $this->defaultSuccessMessage)
            ->setStatusCode($code)
            ->send();
    }

    /**
     * Helper method untuk response error cepat.
     */
    public function error(string $message = null, int $code = 500, $errors = null): JsonResponse
    {
        return $this->apiResponse()
            ->setMessage($message ?? $this->defaultErrorMessage)
            ->setStatusCode($code)
            ->withErrors($errors)
            ->send();
    }

    /**
     * Helper untuk response not found.
     */
    public function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Helper untuk response validation error.
     */
    public function validationError($errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Helper untuk response unauthorized.
     */
    public function unauthorized(string $message = 'Tidak diizinkan'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Helper untuk response forbidden.
     */
    public function forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Sanitasi data respons untuk menghindari struktur ['data']['data'].
     */
    protected function sanitizeResponseData($data)
    {
        if (is_array($data) && isset($data['data']) && count($data) === 1) {
            return $data['data']; // Ambil data dalamnya jika hanya ada key 'data'
        }

        return $data;
    }

    /**
     * Tangani exception dan format respons error.
     */
    public function handleException(Exception $e): JsonResponse
    {
        // Log exception untuk debugging
        Log::error('API Exception: ' . $e->getMessage(), [
            'exception' => $e,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        // Handle exception berdasarkan tipe
        if ($e instanceof QueryException) {
            return $this->handleQueryException($e);
        }

        if ($e instanceof ValidationException) {
            return $this->validationError($e->errors(), $e->getMessage());
        }

        return $this->handleGenericException($e);
    }

    /**
     * Handle QueryException dengan detil yang lebih baik.
     */
    protected function handleQueryException(QueryException $e): JsonResponse
    {
        $message = $e->getMessage();

        // Cek berbagai jenis error database
        $handlers = [
            'Duplicate entry' => 'handleDuplicateEntry',
            'SQLSTATE[42S22]' => 'handleMissingColumn',
            'SQLSTATE[42S02]' => 'handleTableNotFound',
            "doesn't have a default value" => 'handleMissingDefaultValue',
            'Incorrect date value' => 'handleInvalidDateFormat',
            'cannot be null' => 'handleNotNullableColumn',
        ];

        foreach ($handlers as $pattern => $method) {
            if (str_contains($message, $pattern)) {
                return $this->$method($message);
            }
        }

        return $this->handleGenericException($e);
    }

    /**
     * Handle exception umum.
     */
    protected function handleGenericException(Exception $e): JsonResponse
    {
        $isDebug = config('app.debug');
        $code = $e->getCode();
        $statusCode = ($code >= 100 && $code < 600) ? $code : 500;

        $errorDetail = $isDebug ? [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ] : [];

        return $this->error(
            $isDebug ? $e->getMessage() : $this->defaultErrorMessage,
            $statusCode,
            $errorDetail
        );
    }

    /**
     * Handle duplicate entry error.
     */
    protected function handleDuplicateEntry(string $message): JsonResponse
    {
        $isDebug = config('app.debug');
        $fieldName = $this->parseDuplicateKey($message);

        $errorDetail = $isDebug ? [
            'detail' => $fieldName,
            'message' => $message,
        ] : [];

        return $this->error(
            "Data sudah ada (duplikat): {$fieldName}.",
            409,
            $errorDetail
        );
    }

    /**
     * Parse duplicate key dari pesan error.
     */
    protected function parseDuplicateKey(string $message): string
    {
        preg_match("/for key '(.+?)'/", $message, $matches);
        if (isset($matches[1])) {
            $key = $matches[1];
            // Coba ekstrak nama field dari constraint name
            if (preg_match('/_(\w+)_unique$/', $key, $m)) {
                return $m[1];
            }
            return $key;
        }
        return 'unknown';
    }

    /**
     * Handle missing column error.
     */
    protected function handleMissingColumn(string $message): JsonResponse
    {
        $isDebug = config('app.debug');
        preg_match("/Unknown column '(.+?)'/", $message, $matchesCol);
        preg_match("/insert into `(.+?)`/", $message, $matchesTable);

        $missingColumn = $matchesCol[1] ?? 'unknown';
        $tableName = $matchesTable[1] ?? 'unknown';

        $errorDetail = $isDebug ? [
            'detail' => "Kolom '{$missingColumn}' belum ditambahkan di tabel '{$tableName}'.",
            'message' => $message,
        ] : [];

        return $this->error(
            $isDebug ? "Kolom database tidak ditemukan: {$missingColumn}" : $this->defaultErrorMessage,
            500,
            $errorDetail
        );
    }

    /**
     * Handle not nullable column error.
     */
    protected function handleNotNullableColumn(string $message): JsonResponse
    {
        $isDebug = config('app.debug');
        preg_match("/Column '(.+?)' cannot be null/", $message, $matches);
        $column = $matches[1] ?? 'unknown';

        $errorDetail = $isDebug ? [
            'detail' => "Kolom '{$column}' bersifat wajib (NOT NULL).",
            'message' => $message,
        ] : [];

        return $this->error(
            "Kolom '{$column}' tidak boleh kosong.",
            422,
            $errorDetail
        );
    }

    /**
     * Handle table not found error.
     */
    protected function handleTableNotFound(string $message): JsonResponse
    {
        $isDebug = config('app.debug');
        preg_match("/Table '(.+?)'/", $message, $matches);
        $tableName = $matches[1] ?? 'unknown';

        $errorDetail = $isDebug ? [
            'detail' => "Tabel '{$tableName}' belum dibuat atau tidak tersedia di database.",
            'message' => $message,
        ] : [];

        return $this->error(
            $isDebug ? "Tabel database tidak ditemukan: {$tableName}" : $this->defaultErrorMessage,
            500,
            $errorDetail
        );
    }

    /**
     * Handle missing default value error.
     */
    protected function handleMissingDefaultValue(string $message): JsonResponse
    {
        $isDebug = config('app.debug');
        preg_match("/Field '(.+?)' doesn't have a default value/", $message, $matches);
        $field = $matches[1] ?? 'unknown';

        $errorDetail = $isDebug ? [
            'detail' => "Kolom '{$field}' tidak boleh kosong dan tidak memiliki nilai default di database.",
            'message' => $message,
        ] : [];

        return $this->error(
            "Kolom '{$field}' wajib diisi.",
            422,
            $errorDetail
        );
    }

    /**
     * Handle invalid date format error.
     */
    protected function handleInvalidDateFormat(string $message): JsonResponse
    {
        $isDebug = config('app.debug');
        preg_match("/Incorrect date value: '(.+?)' for column `.+?`\.`.+?`\.`(.+?)`/", $message, $matches);
        $invalidValue = $matches[1] ?? 'unknown';
        $column = $matches[2] ?? 'unknown';

        $errorDetail = $isDebug ? [
            'detail' => "Nilai '{$invalidValue}' tidak sesuai format 'YYYY-MM-DD'.",
            'message' => $message,
        ] : [];

        return $this->error(
            "Format tanggal tidak valid untuk kolom '{$column}'.",
            422,
            $errorDetail
        );
    }
}