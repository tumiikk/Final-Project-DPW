<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Koneksi database MySQL memakai PDO (pengganti Eloquent/Query Builder Laravel).
 * Semua query yang menerima input pengguna WAJIB memakai prepared statement (parameter ? atau :nama).
 */
final class Database
{
    private static ?PDO $pdo = null;

    /** Membuka koneksi sekali lalu dipakai ulang selama request berjalan. */
    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $c = config('app.database');
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $c['host'], $c['port'], $c['database'], $c['charset']);

        // Menghubungkan aplikasi dengan database MySQL
        self::$pdo = new PDO($dsn, $c['username'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // error SQL dilempar sebagai exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // hasil query berupa array asosiatif
            PDO::ATTR_EMULATE_PREPARES => false,               // prepared statement asli dari MySQL
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);

        // Karakter set & mode ketat yang sama dengan konfigurasi Laravel asli (strict => true)
        self::$pdo->exec("SET NAMES {$c['charset']} COLLATE {$c['collation']}");
        self::$pdo->exec("SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        return self::$pdo;
    }

    /** Menjalankan query dengan parameter (prepared statement) dan mengembalikan statement-nya. */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /** Mengambil satu baris (atau null bila tidak ada). */
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    /** Mengambil semua baris. */
    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Mengambil satu nilai (kolom pertama baris pertama), contoh hasil COUNT(*). */
    public static function value(string $sql, array $params = []): mixed
    {
        $v = self::query($sql, $params)->fetchColumn();

        return $v === false ? null : $v;
    }

    /** Menjalankan INSERT lalu mengembalikan id baris baru. */
    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);

        return (int) self::pdo()->lastInsertId();
    }

    /**
     * Menjalankan beberapa query dalam satu transaksi: semua berhasil atau semua dibatalkan.
     * Transaksi bersarang tidak membuka transaksi baru (ikut transaksi luar).
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            return $callback();
        }

        $pdo->beginTransaction();
        try {
            $result = $callback();
            $pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** True bila error berasal dari pelanggaran UNIQUE (kode MySQL 1062), mis. token tamu kembar. */
    public static function isDuplicate(PDOException $e): bool
    {
        return ($e->errorInfo[1] ?? null) === 1062;
    }
}
