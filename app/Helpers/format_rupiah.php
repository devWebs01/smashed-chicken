<?php

// app/Helpers/helpers.php

if (! function_exists('formatRupiah')) {
    /**
     * Format angka ke Rupiah, misal 15000 → "Rp 15.000"
     *
     * @param  int|float  $angka
     * @param  bool  $withPrefix  Sertakan "Rp" di depan
     * @return string
     */
    function formatRupiah($angka, $withPrefix = true)
    {
        $hasil = number_format($angka, 0, ',', '.');

        return $withPrefix
            ? 'Rp '.$hasil
            : $hasil;
    }
}
