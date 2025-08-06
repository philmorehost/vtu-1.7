<?php

// migrations/005_seed_service_products.php

return [
    "up" => function($pdo) {
        $products = [
            ['data', 'MTN 1GB', 'mtn-1gb', 300, 'MTN'],
            ['data', 'Glo 1GB', 'glo-1gb', 250, 'Glo'],
            ['airtime', 'MTN Airtime', 'mtn-airtime', 0, 'MTN'],
            ['airtime', 'Glo Airtime', 'glo-airtime', 0, 'Glo'],
            ['exam', 'WAEC Result Checker', 'waec-checker', 5000, null],
            ['exam', 'NECO Result Token', 'neco-token', 4500, null],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO service_products (service_type, name, plan_code, selling_price, network_id)
            VALUES (?, ?, ?, ?, (SELECT id FROM networks WHERE name = ?))
        ");

        foreach ($products as $product) {
            $stmt->execute($product);
        }
    },
    "down" => function($pdo) {
        // Do nothing
    }
];
