<?php
declare(strict_types=1);

/**
 * Metode pembayaran — kode disimpan di payments.payment_method
 */
function payment_method_labels(): array
{
    return [
        'bank_transfer' => 'Transfer Bank',
        'virtual_account' => 'Virtual Account (VA)',
        'qris' => 'QRIS',
        'ewallet' => 'E-Wallet (OVO, GoPay, DANA, ShopeePay, dll.)',
    ];
}

function payment_method_label(string $code): string
{
    return payment_method_labels()[$code] ?? $code;
}

function valid_payment_method(?string $code): bool
{
    return is_string($code) && array_key_exists($code, payment_method_labels());
}

/**
 * Teks panduan singkat per metode (bisa disesuaikan untuk produksi).
 */
function payment_method_instructions(string $code, string $bookingCode, string $amountFormatted): array
{
    $bc = $bookingCode;
    $amt = $amountFormatted;

    $map = [
        'bank_transfer' => [
            'Transfer ke rekening Bank BCA nomor 1234567890 atas nama PT Rezky Maskapai.',
            'Nominal tepat: ' . $amt . '. Di berita transfer tulis: ' . $bc . '.',
            'Setelah transfer, unggah bukti (screenshot atau PDF) di bawah.',
        ],
        'virtual_account' => [
            'Gunakan Virtual Account di channel bank / mobile banking Anda (nomor VA dapat dikirim lewat email/SMS pada sistem produksi).',
            'Bayar tepat ' . $amt . ' sebelum batas waktu.',
            'Simpan bukti pembayaran lalu unggah di halaman ini.',
        ],
        'qris' => [
            'Buka aplikasi bank atau e-wallet Anda, pilih bayar dengan QRIS.',
            'Scan QR dan bayar nominal ' . $amt . ' sesuai tagihan.',
            'Unggah screenshot bukti sukses bayar.',
        ],
        'ewallet' => [
            'Buka aplikasi e-wallet (OVO, GoPay, DANA, ShopeePay, dlls.), lakukan pembayaran senilai ' . $amt . '.',
            'Cantumkan kode ' . $bc . ' di keterangan jika tersedia.',
            'Unggah bukti transaksi dari aplikasi.',
        ],
    ];

    return $map[$code] ?? [
        'Lakukan pembayaran sebesar ' . $amt . ' sesuai metode yang Anda pilih.',
        'Setelah itu unggah bukti di halaman ini.',
    ];
}
