<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Generator nomor dokumen yang aman terhadap race condition.
 *
 * PRD/AGENTS.md: "Jangan gunakan count()+1 untuk nomor dokumen
 * (gunakan urutan aman dari race condition)."
 *
 * Implementasi memakai tabel `document_sequences` dengan `lockForUpdate()`
 * sehingga increment bersifat atomik antar request bersamaan.
 */
class DocumentNumberService
{
    /**
     * Ambil nomor berikutnya untuk sebuah tipe dokumen.
     *
     * Format: {prefix}-{period}-{nomor 5 digit}, contoh: PR-202610-00042
     *
     * @param  string  $key   Kunci unik jenis dokumen (mis. 'purchase_request').
     * @param  string  $prefix Prefix nomor (mis. 'PR').
     * @param  string|null  $period  Periode (mis. '202610'). Null = tidak ada periode.
     */
    public function next(string $key, string $prefix, ?string $period = null): string
    {
        $period = $this->normalizePeriod($period);

        $number = DB::transaction(function () use ($key, $prefix, $period) {
            // Pastikan baris ada (aman karena unik per key+period; retry bila concurrent insert).
            $this->ensureRowExists($key, $prefix, $period);

            $sequence = DB::table('document_sequences')
                ->where('key', $key)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                // Baris terhapus/di-migrate saat request berjalan — guard agar tidak fatal.
                throw new \RuntimeException("Document sequence [{$key}/{$period}] tidak ditemukan.");
            }

            $next = ((int) ($sequence->last_number ?? 0)) + 1;

            DB::table('document_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'last_number' => $next,
                    'updated_at' => now(),
                ]);

            return $next;
        });

        return $this->format($prefix, $period, $number);
    }

    /**
     * Format nomor dokumen.
     */
    public function format(string $prefix, ?string $period, int $number): string
    {
        $parts = array_filter([$prefix, $period], fn ($part) => $part !== null && $part !== '');

        return implode('-', $parts).'-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Normalisasi periode menjadi string (tanpa null) agar cocok dengan kolom.
     */
    protected function normalizePeriod(?string $period): string
    {
        return $period === null ? '' : $period;
    }

    /**
     * Pastikan baris sequence tersedia (idempotent).
     */
    protected function ensureRowExists(string $key, string $prefix, string $period): void
    {
        $exists = DB::table('document_sequences')
            ->where('key', $key)
            ->where('period', $period)
            ->exists();

        if ($exists) {
            return;
        }

        try {
            DB::table('document_sequences')->insert([
                'key' => $key,
                'prefix' => $prefix,
                'period' => $period,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // Baris dibuat oleh request bersamaan — abaikan.
        }
    }
}
