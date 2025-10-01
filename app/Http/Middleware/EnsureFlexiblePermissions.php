<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware AuthorizeAdvanced
 * 
 * Kegunaan:
 * Middleware ini digunakan untuk menangani skenario otorisasi (authorization) yang lebih kompleks pada aplikasi Laravel,
 * khususnya ketika Anda ingin memeriksa apakah user memiliki satu atau lebih permission dengan kondisi AND (semua permission harus dimiliki)
 * atau OR (cukup salah satu permission saja yang dimiliki). 
 * 
 * Kenapa harus ada:
 * Middleware ini penting karena dalam aplikasi modern, kebutuhan otorisasi seringkali tidak cukup hanya dengan satu permission saja.
 * Ada kasus di mana akses ke suatu resource membutuhkan kombinasi beberapa permission (AND), atau cukup salah satu dari beberapa permission (OR).
 * Dengan adanya middleware ini, developer dapat dengan mudah mengatur logika otorisasi yang fleksibel dan mudah dibaca langsung di route.
 * 
 * Contoh penggunaan:
 * - authorize.any:user.view,user.create   // user harus punya salah satu permission (OR)
 * - authorize:user.view|user.create       // user harus punya semua permission (AND)
 * - authorize:user.view                   // user harus punya permission user.view
 */
class EnsureFlexiblePermissions
{
    /**
     * Fungsi utama middleware.
     * Memeriksa permission string yang diberikan, lalu menentukan apakah user lolos otorisasi berdasarkan kondisi AND/OR.
     * 
     * @param Request $request
     * @param Closure $next
     * @param string|null $permissionString
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $permissionString = null): Response
    {
        if ($permissionString === null) {
            // Jika tidak ada permission yang dicek, lanjutkan request.
            return $next($request);
        }
        
        $user = $request->user();
        
        if (!$user) {
            abort(401, 'Autentikasi diperlukan.');
        }
        
        // Jika permission dipisahkan dengan koma, berarti OR condition (cukup salah satu permission)
        if (str_contains($permissionString, ',')) {
            $permissions = explode(',', $permissionString);
            return $this->handleOrCondition($user, $permissions, $next, $request);
        }
        
        // Jika permission dipisahkan dengan pipe, berarti AND condition (semua permission harus dimiliki)
        if (str_contains($permissionString, '|')) {
            $permissions = explode('|', $permissionString);
            return $this->handleAndCondition($user, $permissions, $next, $request);
        }

        // Jika hanya satu permission
        return $this->handleSinglePermission($user, $permissionString, $next, $request);
    }

    /**
     * Handle OR condition: user cukup punya salah satu permission.
     * 
     * @param $user
     * @param array $permissions
     * @param Closure $next
     * @param Request $request
     * @return Response
     */
    protected function handleOrCondition($user, array $permissions, Closure $next, Request $request): Response
    {
        foreach ($permissions as $permission) {
            $permission = $this->normalizePermission(trim($permission));
            if ($permission === '') {
                continue;
            }
            try {
                if ($user->hasPermissionTo($permission)) {
                    // Jika user punya salah satu permission, lanjutkan request.
                    return $next($request);
                }
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                // Jika permission tidak ada, lanjut cek permission berikutnya.
                continue;
            }
        }

        // Jika tidak ada satupun permission yang dimiliki user
        abort(403, "Anda memerlukan salah satu permission berikut: " . implode(', ', array_filter(array_map([$this, 'normalizePermission'], $permissions))));
    }

    /**
     * Handle AND condition: user harus punya semua permission.
     * 
     * @param $user
     * @param array $permissions
     * @param Closure $next
     * @param Request $request
     * @return Response
     */
    protected function handleAndCondition($user, array $permissions, Closure $next, Request $request): Response
    {

        foreach ($permissions as $permission) {
            $permission = $this->normalizePermission(trim($permission));
            if ($permission === '') {
                continue;
            }
            try {
                if (!$user->hasPermissionTo($permission)) {
                    // Jika salah satu permission tidak dimiliki, tolak akses.
                    abort(403, "Permission yang dibutuhkan: {$permission}");
                }
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                abort(403, "Permission '{$permission}' tidak ditemukan.");
            }
        }

        // Jika semua permission dimiliki user
        return $next($request);
    }

    /**
     * Handle single permission: user harus punya permission tersebut.
     * 
     * @param $user
     * @param string $permission
     * @param Closure $next
     * @param Request $request
     * @return Response
     */
    protected function handleSinglePermission($user, string $permission, Closure $next, Request $request): Response
    {
        $permission = $this->normalizePermission(trim($permission));
        if ($permission === '') {
            abort(403, "Permission tidak valid.");
        }
        try {
            if (!$user->hasPermissionTo($permission)) {
                abort(403, "Anda memerlukan permission: {$permission}");
            }
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            abort(403, "Permission '{$permission}' tidak ditemukan.");
        }

        return $next($request);
    }

    /**
     * Mendukung format legacy (view_users, edit_users) dan format baru (user.view, user.create).
     * Jika format sudah pakai titik (user.view), biarkan.
     * Jika format pakai spasi atau underscore (view_users, edit_users), konversi ke user.view, user.edit, dst.
     * 
     * @param string $permission
     * @return string
     */
    protected function normalizePermission(string $permission): string
    {
        $permission = trim($permission);

        // Jika kosong, langsung return kosong
        if ($permission === '') {
            return '';
        }

        // Jika sudah format user.view, user.create, dll, biarkan
        if (preg_match('/^[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+$/', $permission)) {
            return $permission;
        }

        // Jika format legacy: view_users, edit_users, dst
        if (preg_match('/^(view|edit|create|delete)_(.+)$/', $permission, $matches)) {
            $action = $matches[1];
            $entity = $matches[2];
            // singularize entity jika diakhiri s
            $entity = rtrim($entity, 's');
            return "{$entity}.{$action}";
        }

        // Jika format legacy: view users, edit users, dst
        if (preg_match('/^(view|edit|create|delete)\s+(.+)$/', $permission, $matches)) {
            $action = $matches[1];
            $entity = $matches[2];
            $entity = str_replace(' ', '', $entity);
            $entity = rtrim($entity, 's');
            return "{$entity}.{$action}";
        }

        // Jika format role.view, permission.edit, menu.create, dll
        if (preg_match('/^[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+$/', $permission)) {
            return $permission;
        }

        // Default: kembalikan apa adanya
        return $permission;
    }
}