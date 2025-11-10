<?php

namespace App\Services\Odontologia;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static array $hidden = ['password', 'remember_token', 'token'];

    public static function created(string $auditableType, int|string $auditableId, string $user_name, array $new = []): void
    {
        self::write($auditableType, (int)$auditableId, $user_name, 'created', null, $new);
    }

    public static function updated(string $auditableType, int|string $auditableId, string $user_name, array $old, array $new): void
    {
        [$oldDiff, $newDiff] = self::diff($old, $new);
        if (!empty($newDiff)) {
            self::write($auditableType, (int)$auditableId, $user_name, 'updated', $oldDiff, $newDiff);
        }
    }

    public static function deleted(string $auditableType, int|string $auditableId, string $user_name, array $old): void
    {
        self::write($auditableType, (int)$auditableId, $user_name, 'deleted', $old, null);
    }

    private static function write(string $type, int $id, string $user_name,string $event, ?array $old, ?array $new): void
    {
        // filtra campos sensíveis
        if ($old) foreach (self::$hidden as $k) unset($old[$k]);
        if ($new) foreach (self::$hidden as $k) unset($new[$k]);

        // JSON com tratamento de UTF-8
        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;
        $jsonOld = $old ? json_encode($old, $jsonFlags) : null;
        $jsonNew = $new ? json_encode($new, $jsonFlags) : null;

        $req = request();

        DB::table('FAESA_CLINICA_ODONTOLOGIA_AUDITORIA')->insert([
            'AUDITABLE_TYPE' => $type,
            'AUDITABLE_ID'   => $id,                
            'USER_NAME'        => $user_name,
            'EVENT'          => $event,
            'OLD_VALUES'     => $jsonOld,
            'NEW_VALUES'     => $jsonNew,
            'IP'             => $req?->ip(),
            'USER_AGENT'     => $req?->userAgent(),
            'URL'            => $req?->fullUrl(),
            'created_at'     => now(),            
            'updated_at'     => now(),
        ]);
    }

    private static function diff(array $old, array $new): array
    {
        $old = self::normalize($old);
        $new = self::normalize($new);
        $oldDiff = $newDiff = [];
        foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;
            if ($ov !== $nv) {
                $oldDiff[$k] = $ov;
                $newDiff[$k] = $nv;
            }
        }
        return [$oldDiff, $newDiff];
    }

    private static function normalize(array $row): array
    {
        return json_decode(json_encode($row), true);
    }

    public static function login(string $auditableType, int|string $auditableId,string $user_name,array $payload): void
    {
        self::write($auditableType, (int)$auditableId, $user_name ,'login', null, $payload);
    }

    public static function logout(string $auditableType, int|string $auditableId,string $user_name, array $payload): void
    {
        self::write($auditableType, (int)$auditableId, $user_name ,'logout', null, $payload);
    }
}
