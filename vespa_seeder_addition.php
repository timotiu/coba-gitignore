<?php

// Your existing code continues here, then add this VESPA section:

// ===================================================================================
// BAGIAN VESPA: VESPA MATIC
// ===================================================================================

// Query all needed MotorPart objects for VESPA MATIC
$partVespa_Depan = MotorPart::where('name', 'DEPAN')->where('motor_type_id', $mtVesmet->id)->first();
$partVespa_Belakang = MotorPart::where('name', 'BELAKANG')->where('motor_type_id', $mtVesmet->id)->first();

$motorTypeId = $mtVesmet->id;
$harga = [
    'rebound_depan' => 350000,
    'rebound_belakang' => 350000,
    'downsize_depan' => 350000,
    'downsize_belakang' => 350000,
    'maintenance_depan' => 200000,
    'maintenance_belakang' => 200000,
    'paket_reb_dz_depan' => 450000,
    'paket_reb_dz_belakang' => 450000,
];

$parts = [
    'depan' => $partVespa_Depan->id,
    'belakang' => $partVespa_Belakang->id,
];

// --- SINGLE SERVICES ---
$servicePackages = array_merge($servicePackages, [
    // REBOUND
    ['part_included' => null, 'name' => "REBOUND DEPAN", 'motor_part_ids' => json_encode([$parts['depan']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_depan'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
    ['part_included' => null, 'name' => "REBOUND BELAKANG", 'motor_part_ids' => json_encode([$parts['belakang']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['rebound_belakang'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
    
    // DOWNSIZE
    ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE DEPAN", 'motor_part_ids' => json_encode([$parts['depan']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_depan'], 'warranty' => json_encode(['depan' => '1 MINGGU'])],
    ['part_included' => json_encode($part_included), 'name' => "DOWNSIZE BELAKANG", 'motor_part_ids' => json_encode([$parts['belakang']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['downsize_belakang'], 'warranty' => json_encode(['belakang' => '1 MINGGU'])],
    
    // MAINTENANCE
    ['part_included' => null, 'name' => "MAINTENANCE DEPAN", 'motor_part_ids' => json_encode([$parts['depan']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_depan'], 'warranty' => json_encode(['depan' => '3 BULAN'])],
    ['part_included' => null, 'name' => "MAINTENANCE BELAKANG", 'motor_part_ids' => json_encode([$parts['belakang']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['maintenance_belakang'], 'warranty' => json_encode(['belakang' => '3 BULAN'])],
]);

// --- PAKET REB + DZ (SATUAN & KOMBINASI) ---
$servicePackages = array_merge($servicePackages, [
    // SATUAN
    ['part_included' => null, 'name' => "PAKET REB + DZ DEPAN", 'motor_part_ids' => json_encode([$parts['depan']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan'], 'warranty' => json_encode(['depan' => '6 BULAN'])],
    ['part_included' => null, 'name' => "PAKET REB + DZ BELAKANG", 'motor_part_ids' => json_encode([$parts['belakang']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_belakang'], 'warranty' => json_encode(['belakang' => '6 BULAN'])],
    
    // KOMBINASI
    ['part_included' => null, 'name' => "PAKET REB + DZ DEPAN + BELAKANG", 'motor_part_ids' => json_encode([$parts['depan'], $parts['belakang']]), 'motor_type_id' => $motorTypeId, 'type_service' => 'SUSPENSI', 'total_price' => $harga['paket_reb_dz_depan'] + $harga['paket_reb_dz_belakang'], 'warranty' => json_encode(['depan' => '6 BULAN', 'belakang' => '6 BULAN'])],
]);

// --- SEMUA KOMBINASI LAYANAN (DEPAN + BELAKANG) ---
$kombinasiDepan = [
    'REBOUND DEPAN' => ['harga' => $harga['rebound_depan'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan']],
    'DOWNSIZE DEPAN' => ['harga' => $harga['downsize_depan'], 'garansi' => '1 MINGGU', 'part_id' => $parts['depan']],
    'MAINTENANCE DEPAN' => ['harga' => $harga['maintenance_depan'], 'garansi' => '3 BULAN', 'part_id' => $parts['depan']],
];

$kombinasiBelakang = [
    'REBOUND BELAKANG' => ['harga' => $harga['rebound_belakang'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang']],
    'DOWNSIZE BELAKANG' => ['harga' => $harga['downsize_belakang'], 'garansi' => '1 MINGGU', 'part_id' => $parts['belakang']],
    'MAINTENANCE BELAKANG' => ['harga' => $harga['maintenance_belakang'], 'garansi' => '3 BULAN', 'part_id' => $parts['belakang']],
];

foreach ($kombinasiDepan as $namaDepan => $depan) {
    foreach ($kombinasiBelakang as $namaBelakang => $belakang) {
        $isDownsizeCombo = (strpos($namaDepan, 'DOWNSIZE') !== false) || (strpos($namaBelakang, 'DOWNSIZE') !== false);
        $servicePackages[] = [
            'part_included' => $isDownsizeCombo ? json_encode($part_included) : null,
            'name' => "$namaDepan + $namaBelakang",
            'motor_part_ids' => json_encode([$depan['part_id'], $belakang['part_id']]),
            'motor_type_id' => $motorTypeId,
            'type_service' => 'SUSPENSI',
            'total_price' => $depan['harga'] + $belakang['harga'],
            'warranty' => json_encode(['depan' => $depan['garansi'], 'belakang' => $belakang['garansi']]),
        ];
    }
}

// Continue with your existing code...
// (OHLINS sections and final insert remain the same)

?>