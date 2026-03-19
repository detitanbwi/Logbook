<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KpiMasterFactory extends Factory
{
    public function definition(): array
    {
        $kpiNames = [
            'Menyelesaikan Laporan Bulanan',
            'Menganalisis Data Penjualan',
            'Mengembangkan Fitur Aplikasi Baru',
            'Melakukan Pengujian Sistem',
            'Menyiapkan Presentasi Klien',
            'Optimasi Database Server',
            'Membuat Desain UI/UX',
            'Melakukan Audit Keamanan',
            'Mengelola Keluhan Pelanggan',
            'Pembaruan Dokumentasi Proyek',
            'Menyusun Rencana Anggaran',
            'Mengadakan Rapat Evaluasi',
            'Meninjau Kode (Code Review)',
            'Memperbaiki Bug Kritis',
            'Melakukan Pelatihan Karyawan',
            'Menyusun Strategi Pemasaran',
            'Evaluasi Kinerja Vendor',
            'Meningkatkan SEO Website',
            'Mengelola Media Sosial',
            'Melakukan Riset Pasar',
        ];

        return [
            'nama' => fake('id_ID')->randomElement($kpiNames).' '.$this->faker->word(),
            'target_angka' => fake()->randomFloat(2, 1, 250),
            'satuan' => fake()->randomElement(['dokumen', 'jam', 'unit', 'laporan', 'tiket']),
            'deskripsi' => fake('id_ID')->sentence(),
            'status_aktif' => true,
        ];
    }
}
