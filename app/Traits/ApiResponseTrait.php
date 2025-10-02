<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Trait ApiResponseTrait
 * Trait ini menyediakan helper untuk membangun response API yang konsisten, efisien, dan mudah digunakan.
 */
trait ApiResponseTrait
{
    protected array $response = [];
    protected int $statusCode = 200;
    protected array $meta = [];
    protected string $defaultSuccessMessage = 'Request berhasil diproses';
    protected string $defaultErrorMessage = 'Terjadi kesalahan pada server';

    /**
     * Inisialisasi response API dengan data, status, dan pesan.
     * @param mixed $data
     * @return self
     */
    public function apiResponse($data = null): self
    {
        $this->resetResponse();

        $message = null;
        if (is_array($data)) {
            $this->statusCode = $data['statusCode'] ?? $this->statusCode;
            $message = $data['message'] ?? null;
            unset($data['statusCode'], $data['message']);
        }

        $success = $this->isSuccessfulStatusCode($this->statusCode);
        $this->response = [
            'status' => $success ? 'success' : 'error',
            'success' => $success,
            'data' => $this->sanitizeResponseData($data),
            'message' => $message ?? ($success ? $this->defaultSuccessMessage : $this->defaultErrorMessage),
        ];
        return $this;
    }

    /**
     * Reset seluruh properti response ke nilai awal.
     */
    protected function resetResponse(): void
    {
        $this->response = [];
        $this->statusCode = 200;
        $this->meta = [];
    }

    /**
     * Cek apakah status code termasuk sukses (2xx).
     * @param int $code
     * @return bool
     */
    protected function isSuccessfulStatusCode(int $code): bool
    {
        return $code >= 200 && $code < 300;
    }

    /**
     * Set pesan pada response.
     * @param string $message
     * @return self
     */
    public function setMessage(string $message): self
    {
        $this->response['message'] = $message;
        return $this;
    }

    /**
     * Tambahkan meta data ke response.
     * @param array $meta
     * @return self
     */
    public function addMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Tambahkan meta data pagination dari objek LengthAwarePaginator.
     * @param $paginatedData
     * @return self
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
     * Tambahkan error detail ke response.
     * @param mixed $errors
     * @return self
     */
    public function withErrors($errors): self
    {
        if ($errors !== null) {
            $this->response['errors'] = $errors;
        }
        $this->response['success'] = false;
        $this->response['status'] = 'error';
        return $this;
    }

    /**
     * Set status code HTTP pada response.
     * @param int $code
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        $success = $this->isSuccessfulStatusCode($code);
        $this->response['success'] = $success;
        $this->response['status'] = $success ? 'success' : 'error';
        return $this;
    }

    /**
     * Kirim response sebagai JSON dan reset state.
     * @return JsonResponse
     */
    public function send(): JsonResponse
    {
        if (!empty($this->meta)) {
            $this->response['meta'] = $this->meta;
        }
        $response = response()->json($this->response, $this->statusCode);
        $this->resetResponse();
        return $response;
    }

    /**
     * Helper response sukses cepat.
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    public function success($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return $this->apiResponse($data)
            ->setMessage($message ?? $this->defaultSuccessMessage)
            ->setStatusCode($code)
            ->send();
    }

    /**
     * Helper response error cepat.
     * @param string|null $message
     * @param int $code
     * @param mixed $errors
     * @return JsonResponse
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
     * Helper response not found (404).
     * @param string $message
     * @return JsonResponse
     */
    public function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Helper response validation error (422).
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    public function validationError($errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Helper response unauthorized (401).
     * @param string $message
     * @return JsonResponse
     */
    public function unauthorized(string $message = 'Tidak diizinkan'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Helper response forbidden (403).
     * @param string $message
     * @return JsonResponse
     */
    public function forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Sanitasi data agar tidak nested ['data']['data'].
     * @param mixed $data
     * @return mixed
     */
    protected function sanitizeResponseData($data)
    {
        return (is_array($data) && isset($data['data']) && count($data) === 1)
            ? $data['data']
            : $data;
    }

    /**
     * Tangani exception dan kembalikan response error terformat.
     * @param Exception $e
     * @return JsonResponse
     */
    public function handleException(Exception $e): JsonResponse
    {
        Log::error('API Exception: ' . $e->getMessage(), [
            'exception' => $e,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        if ($e instanceof QueryException) {
            return $this->handleQueryException($e);
        }
        if ($e instanceof ValidationException) {
            return $this->validationError($e->errors(), $e->getMessage());
        }
        return $this->handleGenericException($e);
    }

    /**
     * Tangani QueryException dengan deteksi error DB spesifik.
     * @param QueryException $e
     * @return JsonResponse
     */
    protected function handleQueryException(QueryException $e): JsonResponse
    {
        $message = $e->getMessage();
        $patterns = [
            'Duplicate entry' => 'handleDuplicateEntry',
            'SQLSTATE[42S22]' => 'handleMissingColumn',
            'SQLSTATE[42S02]' => 'handleTableNotFound',
            "doesn't have a default value" => 'handleMissingDefaultValue',
            'Incorrect date value' => 'handleInvalidDateFormat',
            'cannot be null' => 'handleNotNullableColumn',
        ];
        foreach ($patterns as $pattern => $method) {
            if (str_contains($message, $pattern)) {
                return $this->$method($message);
            }
        }
        return $this->handleGenericException($e);
    }

    /**
     * Tangani exception umum (fallback).
     * @param Exception $e
     * @return JsonResponse
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
     * Tangani error duplicate entry pada DB.
     * @param string $message
     * @return JsonResponse
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
     * Parse nama field dari pesan duplicate key DB.
     * @param string $message
     * @return string
     */
    protected function parseDuplicateKey(string $message): string
    {
        if (preg_match("/for key '(.+?)'/", $message, $matches)) {
            $key = $matches[1];
            if (preg_match('/_(\w+)_unique$/', $key, $m)) {
                return $m[1];
            }
            return $key;
        }
        return 'unknown';
    }

    /**
     * Tangani error kolom tidak ditemukan pada DB.
     * @param string $message
     * @return JsonResponse
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
     * Tangani error kolom NOT NULL pada DB.
     * @param string $message
     * @return JsonResponse
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
     * Tangani error tabel tidak ditemukan pada DB.
     * @param string $message
     * @return JsonResponse
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
     * Tangani error field tidak punya default value pada DB.
     * @param string $message
     * @return JsonResponse
     */
    protected function handleMissingDefaultValue(string $message): JsonResponse
    {
        $isDebug = config('app.debug');
        preg_match("/Field '(.+?)' doesn't have a default value/", $message, $matches);
        $field = $matches[1] ?? 'unknown';
        $errorDetail = $isDebug ? [
            'message' => "Kolom '{$field}' tidak boleh kosong dan tidak memiliki nilai default di database.",
            'detail' => $message,
        ] : [];
        return $this->error(
            "Kolom '{$field}' wajib diisi.",
            422,
            $errorDetail
        );
    }

    /**
     * Tangani error format tanggal tidak valid pada DB.
     * @param string $message
     * @return JsonResponse
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