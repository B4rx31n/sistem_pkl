<?php

namespace App\Exceptions;

use Exception;

/**
 * Dilempar ketika kuota industri sudah penuh saat membuat penempatan.
 * Ditangkap di PenempatanController agar admin mendapat pesan yang jelas
 * (bukan error 500), sekaligus membatalkan transaksi.
 */
class KuotaPenuhException extends Exception
{
}
