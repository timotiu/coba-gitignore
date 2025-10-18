<?php

use Illuminate\Database\Seeder;
use App\Models\ServicePackage;
use App\Models\MotorType;
use App\Models\MotorPart;

class ServicePackageSeeder extends Seeder
{
    public function run()
    {
        $servicePackages = [];
        $part_included = [];
        foreach (
            [
                ["id" => 46, "name" => "HONDA (PER DOWNSIZE)", "price" => 15000],
                ["id" => 49, "name" => "YAMAHA (PER DOWNSIZE)", "price" => 15000],
                ["id" => 48, "name" => "X-MAX (PER DOWNSIZE)", "price" => 25000],
                ["id" => 47, "name" => "NMAX / PCX (PER DOWNSIZE)", "price" => 25000],
            ] as $item
        ) {
            $part_included[] = json_encode($item);
        }
        $mtBebekMatic = MotorType::where('name', 'BEBEK / MATIC')->first();
        $mtSport = MotorType::where('name', 'SPORT / NACKED / CRUISER')->first();
        $mtTrail = MotorType::where('name', 'TRAIL / ADVENTURE')->first();
        $mtVesmet = MotorType::where('name', 'VESPA MATIC')->first();
        $mtOhlinsMatic = MotorType::where('name', 'OHLINS MATIC')->first();
        $mtOhlinsSport = MotorType::where('name', 'OHLINS SPORT / NAKED / CRUISER')->first();
        $mtOhlinsTrail = MotorType::where('name', 'OHLINS TRAIL / ADVENTURE')->first();

        // Query all needed MotorPart objects for BEBEK / MATIC (110-160cc)
        $partBM_DepanStd_110_160 = MotorPart::where('name', 'DEPAN STD')->where('cc_range', '110-160CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_DepanUsd_110_160 = MotorPart::where('name', 'DEPAN USD')->where('cc_range', '110-160CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_BelakangSingleStdAm_110_160 = MotorPart::where('name', 'BELAKANG (S) STD / AM')->where('cc_range', '110-160CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_BelakangDoubleStdAm_110_160 = MotorPart::where('name', 'BELAKANG (D) STD / AM')->where('cc_range', '110-160CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_BelakangDoubleStd_110_160 = MotorPart::where('name', 'BELAKANG (D) STD')->where('cc_range', '110-160CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_BelakangDoubleAm_110_160 = MotorPart::where('name', 'BELAKANG (D) AM')->where('cc_range', '110-160CC')->where('motor_type_id', $mtBebekMatic->id)->first();

        // ===================================================================================
        // BAGIAN 1: BEBEK / MATIC (110-160cc)
        // ===================================================================================
        $motorTypeId = $mtBebekMatic->id;
        $harga = [
            'rebound_depan_std' => 300000,
            'rebound_depan_usd' => 400000,
            'rebound_belakang_s' => 300000,
            'rebound_belakang_d' => 400000,
            'downsize_depan_std' => 250000,
            'downsize_depan_usd' => 400000,
            'downsize_belakang_s' => 300000,
            'downsize_belakang_d' => 400000,
            'maintenance_depan_std' => 160000,
            'maintenance_depan_usd' => 220000,
            'maintenance_belakang_s' => 160000,
            'maintenance_belakang_d_std' => 200000,
            'maintenance_belakang_d_am' => 250000,
            'paket_reb_dz_depan_std' => 400000,
            'paket_reb_dz_depan_usd' => 500000,
            'paket_reb_dz_belakang_s' => 400000,
            'paket_reb_dz_belakang_d' => 500000,
        ];
        $parts = [
            'depan_std' => $partBM_DepanStd_110_160->id,
            'depan_usd' => $partBM_DepanUsd_110_160->id,
            'belakang_s' => $partBM_BelakangSingleStdAm_110_160->id,
            'belakang_d' => $partBM_BelakangDoubleStdAm_110_160->id,
            'belakang_d_std' => $partBM_BelakangDoubleStd_110_160->id,
            'belakang_d_am' => $partBM_BelakangDoubleAm_110_160->id,
        ];

        // --- SINGLE SERVICES ---
        $servicePackages = array_merge($servicePackages, [
            // REBOUND
            ['part_included' => null, 'name' => "REBOUND DEPAN STD", 'motor_part_ids' => json_encode([$parts['depan_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_std'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND BELAKANG (S) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_s']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_belakang_s'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_belakang_d'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            // DOWNSIZE
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN STD", 'motor_part_ids' => json_encode([$parts['depan_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_std'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_usd'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE BELAKANG (S) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_s']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_belakang_s'], 'warranty' => json_encode(['belakang' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_belakang_d'], 'warranty' => json_encode(['belakang' => '1 MINGGU'])],
            // MAINTENANCE
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN STD", 'motor_part_ids' => json_encode([$parts['depan_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_std'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG (S) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_s']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_s'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG (D) STD", 'motor_part_ids' => json_encode([$parts['belakang_d_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_d_std'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG (D) AM", 'motor_part_ids' => json_encode([$parts['belakang_d_am']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_d_am'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
        ]);

        // --- PAKET REB - DZ (SATUAN & KOMBINASI) ---
        $servicePackages = array_merge($servicePackages, [
            // SATUAN
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN STD", 'motor_part_ids' => json_encode([$parts['depan_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_std'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ BELAKANG (S) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_s']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_belakang_s'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            // KOMBINASI
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN STD + BELAKANG (S) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_std'], $parts['belakang_s']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_std'] + $harga['paket_reb_dz_belakang_s'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN STD + BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_std'], $parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_std'] + $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD + BELAKANG (S) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_usd'], $parts['belakang_s']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'] + $harga['paket_reb_dz_belakang_s'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD + BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_usd'], $parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'] + $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
        ]);
        // --- SEMUA KOMBINASI LAYANAN (DEPAN + BELAKANG) ---
        $kombinasiDepan = [
            'REBOUND DEPAN STD' => ['harga' => $harga['rebound_depan_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_std']],
            'REBOUND DEPAN USD' => ['harga' => $harga['rebound_depan_usd'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_usd']],
            'DOWNSIZE DEPAN STD' => ['harga' => $harga['downsize_depan_std'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_std']],
            'DOWNSIZE DEPAN USD' => ['harga' => $harga['downsize_depan_usd'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_usd']],
            'MAINTENANCE DEPAN STD' => ['harga' => $harga['maintenance_depan_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_std']],
            'MAINTENANCE DEPAN USD' => ['harga' => $harga['maintenance_depan_usd'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_usd']],
        ];
        $kombinasiBelakang = [
            'REBOUND BELAKANG (S) STD / AM' => ['harga' => $harga['rebound_belakang_s'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_s']],
            'REBOUND BELAKANG (D) STD / AM' => ['harga' => $harga['rebound_belakang_d'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_d']],
            'DOWNSIZE BELAKANG (S) STD / AM' => ['harga' => $harga['downsize_belakang_s'], 'garansi' => '1 MINGGU', 'part_id' => $parts['belakang_s']],
            'DOWNSIZE BELAKANG (D) STD / AM' => ['harga' => $harga['downsize_belakang_d'], 'garansi' => '1 MINGGU', 'part_id' => $parts['belakang_d']],
            'MAINTENANCE BELAKANG (S) STD / AM' => ['harga' => $harga['maintenance_belakang_s'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_s']],
            'MAINTENANCE BELAKANG (D) STD' => ['harga' => $harga['maintenance_belakang_d_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_d_std']],
            'MAINTENANCE BELAKANG (D) AM' => ['harga' => $harga['maintenance_belakang_d_am'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_d_am']],
        ];

        foreach ($kombinasiDepan as $namaDepan => $depan) {
            foreach ($kombinasiBelakang as $namaBelakang => $belakang) {
                $isDownsizeCombo = (strpos($namaDepan, 'DOWNSIZE') !== false) || (strpos($namaBelakang, 'DOWNSIZE') !== false);
                $servicePackages[] = [
                    'part_included' => $isDownsizeCombo ? json_encode($part_included) : null,
                    'name' => "$namaDepan + $namaBelakang",
                    'motor_part_ids' => json_encode([$depan['part_id'], $belakang['part_id']]), // Array dari kedua part ID
                    'motor_type_id' => $motorTypeId,
                    'type_service' => 'SUSPENSI',
                    'total_price' => $depan['harga'] + $belakang['harga'],
                    'warranty' => json_encode(['depan' => $depan['garansi'], 'belakang' => $belakang['garansi']]),
                ];
            }
        }

        // ===================================================================================
        // BAGIAN 2: BEBEK / MATIC (200-250cc)
        // ===================================================================================
        // Query all needed MotorPart objects for BEBEK / MATIC (200-250cc)
        $partBM_DepanStd_200_250 = MotorPart::where('name', 'DEPAN STD')->where('cc_range', '200-250CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_DepanUsd_200_250 = MotorPart::where('name', 'DEPAN USD')->where('cc_range', '200-250CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_BelakangDoubleStdAm_200_250 = MotorPart::where('name', 'BELAKANG (D) STD / AM')->where('cc_range', '200-250CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_BelakangDoubleStd_200_250 = MotorPart::where('name', 'BELAKANG (D) STD')->where('cc_range', '200-250CC')->where('motor_type_id', $mtBebekMatic->id)->first();
        $partBM_BelakangDoubleAm_200_250 = MotorPart::where('name', 'BELAKANG (D) AM')->where('cc_range', '200-250CC')->where('motor_type_id', $mtBebekMatic->id)->first();

        $motorTypeId = $mtBebekMatic->id; // Masih sama
        $harga = [
            'rebound_depan_std' => 400000,
            'rebound_depan_usd' => 500000,
            'rebound_belakang_d' => 500000,
            'downsize_depan_std' => 450000,
            'downsize_depan_usd' => 500000,
            'downsize_belakang_d' => 500000,
            'maintenance_depan_std' => 210000,
            'maintenance_depan_usd' => 270000,
            'maintenance_belakang_d' => 210000,
            'maintenance_belakang_d_std' => 250000,
            'maintenance_belakang_d_am' => 300000,
            'paket_reb_dz_depan_std' => 500000,
            'paket_reb_dz_depan_usd' => 600000,
            'paket_reb_dz_belakang_d' => 600000,
        ];
        $parts = [
            'depan_std' => $partBM_DepanStd_200_250->id,
            'depan_usd' => $partBM_DepanUsd_200_250->id,
            'belakang_d' => $partBM_BelakangDoubleStdAm_200_250->id,
            'belakang_d_std' => $partBM_BelakangDoubleStd_200_250->id,
            'belakang_d_am' => $partBM_BelakangDoubleAm_200_250->id,
        ];

        // [SINGLE SERVICES]
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "REBOUND DEPAN STD", 'motor_part_ids' => json_encode([$parts['depan_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_std'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_belakang_d'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN STD", 'motor_part_ids' => json_encode([$parts['depan_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_std'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_usd'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_belakang_d'], 'warranty' => json_encode(['belakang' => '1 MINGGU'])],
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN STD", 'motor_part_ids' => json_encode([$parts['depan_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_std'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG (D) STD", 'motor_part_ids' => json_encode([$parts['belakang_d_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_d_std'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG (D) AM", 'motor_part_ids' => json_encode([$parts['belakang_d_am']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_d_am'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
        ]);

        // [PAKET REB - DZ (SATUAN & KOMBINASI)]
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN STD", 'motor_part_ids' => json_encode([$parts['depan_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_std'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN STD + BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_std'], $parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_std'] + $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD + BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_usd'], $parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'] + $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
        ]);

        // [SEMUA KOMBINASI LAYANAN]
        $kombinasiDepan = [
            'REBOUND DEPAN STD' => ['harga' => $harga['rebound_depan_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_std']],
            'REBOUND DEPAN USD' => ['harga' => $harga['rebound_depan_usd'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_usd']],
            'DOWNSIZE DEPAN STD' => ['harga' => $harga['downsize_depan_std'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_std']],
            'DOWNSIZE DEPAN USD' => ['harga' => $harga['downsize_depan_usd'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_usd']],
            'MAINTENANCE DEPAN STD' => ['harga' => $harga['maintenance_depan_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_std']],
            'MAINTENANCE DEPAN USD' => ['harga' => $harga['maintenance_depan_usd'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_usd']],
        ];

        $kombinasiBelakang = [
            'REBOUND BELAKANG (D) STD / AM' => ['harga' => $harga['rebound_belakang_d'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_d']],
            'DOWNSIZE BELAKANG (D) STD / AM' => ['harga' => $harga['downsize_belakang_d'], 'garansi' => '1 MINGGU', 'part_id' => $parts['belakang_d']],
            'MAINTENANCE BELAKANG (D) STD' => ['harga' => $harga['maintenance_belakang_d_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_d_std']],
            'MAINTENANCE BELAKANG (D) AM' => ['harga' => $harga['maintenance_belakang_d_am'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_d_am']],
        ];
        foreach ($kombinasiDepan as $namaDepan => $depan) {
            foreach ($kombinasiBelakang as $namaBelakang => $belakang) {
                $isDownsizeCombo = (strpos($namaDepan, 'DOWNSIZE') !== false) || (strpos($namaBelakang, 'DOWNSIZE') !== false);
                $servicePackages[] = [
                    'part_included' => $isDownsizeCombo ? json_encode($part_included) : null,
                    'name' => "$namaDepan + $namaBelakang",
                    'motor_part_ids' => json_encode([$depan['part_id'], $belakang['part_id']]), // Array dari kedua part ID
                    'motor_type_id' => $motorTypeId,
                    'type_service' => 'SUSPENSI',
                    'total_price' => $depan['harga'] + $belakang['harga'],
                    'warranty' => json_encode(['depan' => $depan['garansi'], 'belakang' => $belakang['garansi']])
                ];
            }
        }

        // ===================================================================================
        // BAGIAN 3: SPORT / NAKED / CRUISER (150-250cc)
        // ===================================================================================
        $motorTypeId = $mtSport->id;
        // Query all needed MotorPart objects for SPORT / NAKED / CRUISER (150-250cc)
        $partSport_DepanTeleskopik_150_250 = MotorPart::where('name', 'DEPAN TELESKOPIK')->where('cc_range', '150-250CC')->where('motor_type_id', $mtSport->id)->first();
        $partSport_DepanUsd_150_250 = MotorPart::where('name', 'DEPAN USD')->where('cc_range', '150-250CC')->where('motor_type_id', $mtSport->id)->first();
        $partSport_BelakangStd_150_250 = MotorPart::where('name', 'BELAKANG STD')->where('cc_range', '150-250CC')->where('motor_type_id', $mtSport->id)->first();
        $partSport_BelakangAftermarket_150_250 = MotorPart::where('name', 'BELAKANG AFTERMARKET')->where('cc_range', '150-250CC')->where('motor_type_id', $mtSport->id)->first();
        $partSport_BelakangDoubleStdAm_150_250 = MotorPart::where('name', 'BELAKANG (D) STD / AM')->where('cc_range', '150-250CC')->where('motor_type_id', $mtSport->id)->first();

        $harga = [
            'rebound_depan_teleskopik' => 450000,
            'rebound_depan_usd' => 500000,
            'rebound_belakang_std' => 400000,
            'rebound_belakang_am' => 500000,
            'downsize_depan_teleskopik' => 400000,
            'downsize_depan_usd' => 500000,
            'downsize_belakang_std' => 450000,
            'downsize_belakang_d' => 500000,
            'maintenance_depan_teleskopik' => 250000,
            'maintenance_depan_usd' => 300000,
            'maintenance_belakang_std' => 300000,
            'maintenance_belakang_d' => 300000,
            'paket_reb_dz_depan_teleskopik' => 550000,
            'paket_reb_dz_depan_usd' => 650000,
            'paket_reb_dz_belakang_std' => 550000,
            'paket_reb_dz_belakang_d' => 700000,
        ];
        $parts = [
            'depan_teleskopik' => $partSport_DepanTeleskopik_150_250->id,
            'depan_usd' => $partSport_DepanUsd_150_250->id,
            'belakang_std' => $partSport_BelakangStd_150_250->id,
            'belakang_am' => $partSport_BelakangAftermarket_150_250->id,
            'belakang_d' => $partSport_BelakangDoubleStdAm_150_250->id,
        ];

        // [SINGLE SERVICES]
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "REBOUND DEPAN TELESKOPIK", 'motor_part_ids' => json_encode([$parts['depan_teleskopik']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_teleskopik'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND BELAKANG STD", 'motor_part_ids' => json_encode([$parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_belakang_std'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND BELAKANG AFTERMARKET", 'motor_part_ids' => json_encode([$parts['belakang_am']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_belakang_am'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN TELESKOPIK", 'motor_part_ids' => json_encode([$parts['depan_teleskopik']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_teleskopik'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_usd'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE BELAKANG STD", 'motor_part_ids' => json_encode([$parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_belakang_std'], 'warranty' => json_encode(['belakang' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_belakang_d'], 'warranty' => json_encode(['belakang' => '1 MINGGU'])],
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN TELESKOPIK", 'motor_part_ids' => json_encode([$parts['depan_teleskopik']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_teleskopik'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG STD", 'motor_part_ids' => json_encode([$parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_std'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_d'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
        ]);

        // [PAKET REB - DZ (SATUAN & KOMBINASI)]
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN TELESKOPIK", 'motor_part_ids' => json_encode([$parts['depan_teleskopik']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_teleskopik'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ BELAKANG STD", 'motor_part_ids' => json_encode([$parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_belakang_std'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN TELESKOPIK + BELAKANG STD", 'motor_part_ids' => json_encode([$parts['depan_teleskopik'], $parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_teleskopik'] + $harga['paket_reb_dz_belakang_std'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD + BELAKANG STD", 'motor_part_ids' => json_encode([$parts['depan_usd'], $parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'] + $harga['paket_reb_dz_belakang_std'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN TELESKOPIK + BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_teleskopik'], $parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_teleskopik'] + $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD + BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_usd'], $parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'] + $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
        ]);
        // [SEMUA KOMBINASI LAYANAN]
        $kombinasiDepan = [
            'REBOUND DEPAN TELESKOPIK' => ['harga' => $harga['rebound_depan_teleskopik'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_teleskopik']],
            'REBOUND DEPAN USD' => ['harga' => $harga['rebound_depan_usd'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_usd']],
            'DOWNSIZE DEPAN TELESKOPIK' => ['harga' => $harga['downsize_depan_teleskopik'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_teleskopik']],
            'DOWNSIZE DEPAN USD' => ['harga' => $harga['downsize_depan_usd'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_usd']],
            'MAINTENANCE DEPAN TELESKOPIK' => ['harga' => $harga['maintenance_depan_teleskopik'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_teleskopik']],
            'MAINTENANCE DEPAN USD' => ['harga' => $harga['maintenance_depan_usd'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_usd']],
        ];
        $kombinasiBelakang = [
            'REBOUND BELAKANG STD' => ['harga' => $harga['rebound_belakang_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_std']],
            'REBOUND BELAKANG AFTERMARKET' => ['harga' => $harga['rebound_belakang_am'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_am']],
            'DOWNSIZE BELAKANG STD' => ['harga' => $harga['downsize_belakang_std'], 'garansi' => '1 MINGGU', 'part_id' => $parts['belakang_std']],
            'DOWNSIZE BELAKANG (D) STD / AM' => ['harga' => $harga['downsize_belakang_d'], 'garansi' => '1 MINGGU', 'part_id' => $parts['belakang_d']],
            'MAINTENANCE BELAKANG STD' => ['harga' => $harga['maintenance_belakang_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_std']],
            'MAINTENANCE BELAKANG (D) STD / AM' => ['harga' => $harga['maintenance_belakang_d'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_d']],
        ];
        foreach ($kombinasiDepan as $namaDepan => $depan) {
            foreach ($kombinasiBelakang as $namaBelakang => $belakang) {
                $isDownsizeCombo = (strpos($namaDepan, 'DOWNSIZE') !== false) || (strpos($namaBelakang, 'DOWNSIZE') !== false);
                $servicePackages[] = [
                    'part_included' => $isDownsizeCombo ? json_encode($part_included) : null,
                    'name' => "$namaDepan + $namaBelakang",
                    'motor_part_ids' => json_encode([$depan['part_id'], $belakang['part_id']]), // Array dari kedua part ID
                    'motor_type_id' => $motorTypeId,
                    'type_service' => 'SUSPENSI',
                    'total_price' => $depan['harga'] + $belakang['harga'],
                    'warranty' => json_encode(['depan' => $depan['garansi'], 'belakang' => $belakang['garansi']])
                ];
            }
        }

        // ===================================================================================
        // BAGIAN 4: TRAIL / ADVENTURE (150-250cc)
        // ===================================================================================
        $motorTypeId = $mtTrail->id;

        // Query all needed MotorPart objects for TRAIL / ADVENTURE (150-250cc)
        $partTrail_DepanTeleskopik_150_250 = MotorPart::where('name', 'DEPAN TELESKOPIK')->where('cc_range', '150-250CC')->where('motor_type_id', $mtTrail->id)->first();
        $partTrail_DepanUsd_150_250 = MotorPart::where('name', 'DEPAN USD')->where('cc_range', '150-250CC')->where('motor_type_id', $mtTrail->id)->first();
        $partTrail_DepanAmRealJump_150_250 = MotorPart::where('name', 'DEPAN AM / REAL JUMP')->where('cc_range', '150-250CC')->where('motor_type_id', $mtTrail->id)->first();
        $partTrail_BelakangStd_150_250 = MotorPart::where('name', 'BELAKANG STD')->where('cc_range', '150-250CC')->where('motor_type_id', $mtTrail->id)->first();
        $partTrail_BelakangAm_150_250 = MotorPart::where('name', 'BELAKANG AM')->where('cc_range', '150-250CC')->where('motor_type_id', $mtTrail->id)->first();
        $partTrail_BelakangSingleStd_150_250 = MotorPart::where('name', 'BELAKANG (S) STD')->where('cc_range', '150-250CC')->where('motor_type_id', $mtTrail->id)->first();
        $partTrail_BelakangDoubleStdAm_150_250 = MotorPart::where('name', 'BELAKANG (D) STD / AM')->where('cc_range', '150-250CC')->where('motor_type_id', $mtTrail->id)->first();

        $harga = [
            'rebound_depan_teleskopik' => 400000,
            'rebound_depan_usd' => 450000,
            'rebound_depan_am' => 500000,
            'rebound_belakang_std' => 400000,
            'rebound_belakang_am' => 500000,
            'downsize_depan_teleskopik' => 450000,
            'downsize_depan_usd' => 550000,
            'downsize_depan_am' => 600000,
            'downsize_belakang_std' => 450000,
            'downsize_belakang_d' => 500000,
            'maintenance_depan_teleskopik' => 250000,
            'maintenance_depan_usd' => 300000,
            'maintenance_depan_am' => 400000,
            'maintenance_belakang_s' => 300000,
            'maintenance_belakang_d' => 300000,
            // NOTE: HARGA PAKET REB+DZ DI GAMBAR TRAIL SANGAT RENDAH, TAPI SAYA MENGIKUTI DATA GAMBAR.
            'paket_reb_dz_depan_teleskopik' => 500000,
            'paket_reb_dz_depan_usd' => 600000,
            'paket_reb_dz_depan_am' => 700000,
            'paket_reb_dz_belakang_std' => 550000,
            'paket_reb_dz_belakang_d' => 600000,
        ];
        $parts = [
            'depan_teleskopik' => $partTrail_DepanTeleskopik_150_250->id,
            'depan_usd' => $partTrail_DepanUsd_150_250->id,
            'depan_am' => $partTrail_DepanAmRealJump_150_250->id,
            'belakang_std' => $partTrail_BelakangStd_150_250->id,
            'belakang_am' => $partTrail_BelakangAm_150_250->id,
            'belakang_s_std' => $partTrail_BelakangSingleStd_150_250->id,
            'belakang_d' => $partTrail_BelakangDoubleStdAm_150_250->id,
        ];

        // [SINGLE SERVICES]
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "REBOUND DEPAN TELESKOPIK", 'motor_part_ids' => json_encode([$parts['depan_teleskopik']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_teleskopik'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND DEPAN AM", 'motor_part_ids' => json_encode([$parts['depan_am']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan_am'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND BELAKANG STD", 'motor_part_ids' => json_encode([$parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_belakang_std'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND BELAKANG AFTERMARKET", 'motor_part_ids' => json_encode([$parts['belakang_am']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_belakang_am'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN TELESKOPIK", 'motor_part_ids' => json_encode([$parts['depan_teleskopik']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_teleskopik'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_usd'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN AM", 'motor_part_ids' => json_encode([$parts['depan_am']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan_am'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE BELAKANG STD", 'motor_part_ids' => json_encode([$parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_belakang_std'], 'warranty' => json_encode(['belakang' => '1 MINGGU'])],
            ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_belakang_d'], 'warranty' => json_encode(['belakang' => '1 MINGGU'])],
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN TELESKOPIK", 'motor_part_ids' => json_encode([$parts['depan_teleskopik']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_teleskopik'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN AM", 'motor_part_ids' => json_encode([$parts['depan_am']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_am'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG (S) STD", 'motor_part_ids' => json_encode([$parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_s'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang_d'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
        ]);

        // [PAKET REB - DZ (SATUAN & KOMBINASI)]
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN TELESKOPIK", 'motor_part_ids' => json_encode([$parts['depan_teleskopik']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_teleskopik'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD", 'motor_part_ids' => json_encode([$parts['depan_usd']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN AM", 'motor_part_ids' => json_encode([$parts['depan_am']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_am'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ BELAKANG STD", 'motor_part_ids' => json_encode([$parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_belakang_std'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN TELESKOPIK + BELAKANG STD", 'motor_part_ids' => json_encode([$parts['depan_teleskopik'], $parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_teleskopik'] + $harga['paket_reb_dz_belakang_std'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD + BELAKANG STD", 'motor_part_ids' => json_encode([$parts['depan_usd'], $parts['belakang_std']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'] + $harga['paket_reb_dz_belakang_std'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN TELESKOPIK + BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_teleskopik'], $parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_teleskopik'] + $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "PAKET REB - DZ DEPAN USD + BELAKANG (D) STD / AM", 'motor_part_ids' => json_encode([$parts['depan_usd'], $parts['belakang_d']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan_usd'] + $harga['paket_reb_dz_belakang_d'], 'warranty' => json_encode(['depan' => '3 BULAN', 'belakang' => '3 BULAN'])],
        ]);
        // [SEMUA KOMBINASI LAYANAN]
        $kombinasiDepan = [
            'REBOUND DEPAN TELESKOPIK' => ['harga' => $harga['rebound_depan_teleskopik'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_teleskopik']],
            'REBOUND DEPAN AM' => ['harga' => $harga['rebound_depan_am'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_am']],
            'REBOUND DEPAN USD' => ['harga' => $harga['rebound_depan_usd'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_usd']],
            'DOWNSIZE DEPAN TELESKOPIK' => ['harga' => $harga['downsize_depan_teleskopik'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_teleskopik']],
            'DOWNSIZE DEPAN USD' => ['harga' => $harga['downsize_depan_usd'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_usd']],
            'DOWNSIZE DEPAN AM' => ['harga' => $harga['downsize_depan_am'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan_am']],
            'MAINTENANCE DEPAN TELESKOPIK' => ['harga' => $harga['maintenance_depan_teleskopik'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_teleskopik']],
            'MAINTENANCE DEPAN USD' => ['harga' => $harga['maintenance_depan_usd'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_usd']],
            'MAINTENANCE DEPAN AM' => ['harga' => $harga['maintenance_depan_am'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan_am']],
        ];
        $kombinasiBelakang = [
            'REBOUND BELAKANG STD' => ['harga' => $harga['rebound_belakang_std'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_std']],
            'REBOUND BELAKANG AFTERMARKET' => ['harga' => $harga['rebound_belakang_am'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_am']],
            'DOWNSIZE BELAKANG STD' => ['harga' => $harga['downsize_belakang_std'], 'garansi' => '1 MINGGU', 'part_id' => $parts['belakang_std']],
            'DOWNSIZE BELAKANG (D) STD / AM' => ['harga' => $harga['downsize_belakang_d'], 'garansi' => '1 MINGGU', 'part_id' => $parts['belakang_d']],
            'MAINTENANCE BELAKANG (S) STD' => ['harga' => $harga['maintenance_belakang_s'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_std']],
            'MAINTENANCE BELAKANG (D) STD / AM' => ['harga' => $harga['maintenance_belakang_d'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang_d']],
        ];
        foreach ($kombinasiDepan as $namaDepan => $depan) {
            foreach ($kombinasiBelakang as $namaBelakang => $belakang) {
                $isDownsizeCombo = (strpos($namaDepan, 'DOWNSIZE') !== false) || (strpos($namaBelakang, 'DOWNSIZE') !== false);
                $servicePackages[] = [
                    'part_included' => $isDownsizeCombo ? json_encode($part_included) : null,
                    'name' => "$namaDepan + $namaBelakang",
                    'motor_part_ids' => json_encode([$depan['part_id'], $belakang['part_id']]), // Array dari kedua part ID
                    'motor_type_id' => $motorTypeId,
                    'type_service' => 'SUSPENSI',
                    'total_price' => $depan['harga'] + $belakang['harga'],
                    'warranty' => json_encode(['depan' => $depan['garansi'], 'belakang' => $belakang['garansi']])
                ];
            }
        }
        // ===================================================================================
        // BAGIAN 5: OHLINS
        // ===================================================================================
        // Asumsi: Ohlins tidak memiliki kombinasi layanan campur (Rebound+Maintenance)

        // Query all needed MotorPart objects for OHLINS
        $partOhlinsMatic_Single = MotorPart::where('name', 'SINGLE')->where('motor_type_id', $mtOhlinsMatic->id)->first();
        $partOhlinsMatic_Double = MotorPart::where('name', 'DOUBLE')->where('motor_type_id', $mtOhlinsMatic->id)->first();
        $partOhlinsSport_Single = MotorPart::where('name', 'SINGLE')->where('motor_type_id', $mtOhlinsSport->id)->first();
        $partOhlinsSport_Double = MotorPart::where('name', 'DOUBLE')->where('motor_type_id', $mtOhlinsSport->id)->first();
        $partOhlinsTrail_Single = MotorPart::where('name', 'SINGLE')->where('motor_type_id', $mtOhlinsTrail->id)->first();
        $partOhlinsTrail_Double = MotorPart::where('name', 'DOUBLE')->where('motor_type_id', $mtOhlinsTrail->id)->first();

        // OHLINS MATIC
        $harga = ['rebound_single' => 500000, 'rebound_double' => 650000, 'maintenance_single' => 300000, 'maintenance_double' => 400000];
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "REBOUND OHLINS MATIC SINGLE", 'motor_part_ids' => json_encode([$partOhlinsMatic_Single->id]), 'motor_type_id' => $mtOhlinsMatic->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_single'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND OHLINS MATIC DOUBLE", 'motor_part_ids' => json_encode([$partOhlinsMatic_Double->id]), 'motor_type_id' => $mtOhlinsMatic->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_double'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE OHLINS MATIC SINGLE", 'motor_part_ids' => json_encode([$partOhlinsMatic_Single->id]), 'motor_type_id' => $mtOhlinsMatic->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_single'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE OHLINS MATIC DOUBLE", 'motor_part_ids' => json_encode([$partOhlinsMatic_Double->id]), 'motor_type_id' => $mtOhlinsMatic->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_double'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
        ]);

        // OHLINS SPORT
        $harga = ['rebound_single' => 600000, 'rebound_double' => 750000, 'maintenance_single' => 400000, 'maintenance_double' => 500000];
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "REBOUND OHLINS SPORT SINGLE", 'motor_part_ids' => json_encode([$partOhlinsSport_Single->id]), 'motor_type_id' => $mtOhlinsSport->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_single'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND OHLINS SPORT DOUBLE", 'motor_part_ids' => json_encode([$partOhlinsSport_Double->id]), 'motor_type_id' => $mtOhlinsSport->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_double'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE OHLINS SPORT SINGLE", 'motor_part_ids' => json_encode([$partOhlinsSport_Single->id]), 'motor_type_id' => $mtOhlinsSport->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_single'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE OHLINS SPORT DOUBLE", 'motor_part_ids' => json_encode([$partOhlinsSport_Double->id]), 'motor_type_id' => $mtOhlinsSport->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_double'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
        ]);

        // OHLINS TRAIL
        $harga = ['rebound_single' => 650000, 'rebound_double' => 800000, 'maintenance_single' => 450000, 'maintenance_double' => 550000];
        $servicePackages = array_merge($servicePackages, [
            ['part_included' => null, 'name' => "REBOUND OHLINS TRAIL SINGLE", 'motor_part_ids' => json_encode([$partOhlinsTrail_Single->id]), 'motor_type_id' => $mtOhlinsTrail->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_single'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "REBOUND OHLINS TRAIL DOUBLE", 'motor_part_ids' => json_encode([$partOhlinsTrail_Double->id]), 'motor_type_id' => $mtOhlinsTrail->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_double'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE OHLINS TRAIL SINGLE", 'motor_part_ids' => json_encode([$partOhlinsTrail_Single->id]), 'motor_type_id' => $mtOhlinsTrail->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_single'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
            ['part_included' => null, 'name' => "MAINTENANCE OHLINS TRAIL DOUBLE", 'motor_part_ids' => json_encode([$partOhlinsTrail_Double->id]), 'motor_type_id' => $mtOhlinsTrail->id, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_double'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
        ]);

        // ===================================================================================
        // FINAL INSERT KE DATABASE
        // ===================================================================================

        // Menambahkan timestamp
        $now = now();
        foreach ($servicePackages as &$package) {
            $package['created_at'] = $now;
            $package['updated_at'] = $now;
        }

        // Insert semua data dengan satu query efisien
        ServicePackage::insert($servicePackages);
    }
}