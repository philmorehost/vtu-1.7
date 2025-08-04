<?php

// migrations/003_seed_initial_data.php

return [
    "up" => function($pdo) {
        // Seed Networks
        $networks = [
            ['id' => 1, 'name' => 'mtn', 'display_name' => 'MTN', 'code' => 'mtn'],
            ['id' => 2, 'name' => 'glo', 'display_name' => 'GLO', 'code' => 'glo'],
            ['id' => 3, 'name' => 'airtel', 'display_name' => 'Airtel', 'code' => 'airtel'],
            ['id' => 4, 'name' => '9mobile', 'display_name' => '9mobile', 'code' => '9mobile'],
        ];

        $stmt = $pdo->prepare("INSERT INTO networks (id, name, display_name, code) VALUES (?, ?, ?, ?)");
        foreach ($networks as $network) {
            $stmt->execute([$network['id'], $network['name'], $network['display_name'], $network['code']]);
        }

        // Seed Service Providers
        $providers = [
            // Airtime
            ['name' => 'MTN', 'service_type' => 'airtime'],
            ['name' => 'Airtel', 'service_type' => 'airtime'],
            ['name' => 'GLO', 'service_type' => 'airtime'],
            ['name' => '9mobile', 'service_type' => 'airtime'],
            // Bulk SMS
            ['name' => 'MTN', 'service_type' => 'bulksms'],
            ['name' => 'Airtel', 'service_type' => 'bulksms'],
            ['name' => 'GLO', 'service_type' => 'bulksms'],
            ['name' => '9mobile', 'service_type' => 'bulksms'],
            // Data
            ['name' => 'SME', 'service_type' => 'data'],
            ['name' => 'SME2', 'service_type' => 'data'],
            ['name' => 'CG-DATA', 'service_type' => 'data'],
            ['name' => 'DIRECT-DATA (DD)', 'service_type' => 'data'],
            ['name' => 'GIFTING', 'service_type' => 'data'],
            // Cable TV
            ['name' => 'DSTV', 'service_type' => 'cabletv'],
            ['name' => 'GOTV', 'service_type' => 'cabletv'],
            ['name' => 'STARTIMES', 'service_type' => 'cabletv'],
            ['name' => 'SHOWMAX', 'service_type' => 'cabletv'],
            // Betting
            ['name' => 'msport', 'service_type' => 'betting'],
            ['name' => 'naijabet', 'service_type' => 'betting'],
            ['name' => 'nairabet', 'service_type' => 'betting'],
            ['name' => 'bet9ja-agent', 'service_type' => 'betting'],
            ['name' => 'betland', 'service_type' => 'betting'],
            ['name' => 'betlion', 'service_type' => 'betting'],
            ['name' => 'supabet', 'service_type' => 'betting'],
            ['name' => 'bet9ja', 'service_type' => 'betting'],
            ['name' => 'bangbet', 'service_type' => 'betting'],
            ['name' => 'betking', 'service_type' => 'betting'],
            ['name' => '1xbet', 'service_type' => 'betting'],
            ['name' => 'betway', 'service_type' => 'betting'],
            ['name' => 'merrybet', 'service_type' => 'betting'],
            ['name' => 'mlotto', 'service_type' => 'betting'],
            ['name' => 'western-lotto', 'service_type' => 'betting'],
            ['name' => 'hallabet', 'service_type' => 'betting'],
            ['name' => 'green-lotto', 'service_type' => 'betting'],
            // Electric
            ['name' => 'Eko Electric - EKEDC', 'service_type' => 'electricity'],
            ['name' => 'Ikeja Electric - IKEDC', 'service_type' => 'electricity'],
            ['name' => 'Abuja Electric - AEDC', 'service_type' => 'electricity'],
            ['name' => 'Kano Electric - KEDC', 'service_type' => 'electricity'],
            ['name' => 'Porthacourt Electric - PHEDC', 'service_type' => 'electricity'],
            ['name' => 'Jos Electric - JEDC', 'service_type' => 'electricity'],
            ['name' => 'Ibadan Electric - IBEDC', 'service_type' => 'electricity'],
            ['name' => 'Kaduna Elecdtric - KAEDC', 'service_type' => 'electricity'],
            ['name' => 'Enugu Electric - EEDC', 'service_type' => 'electricity'],
            ['name' => 'Benin Electric - BEDC', 'service_type' => 'electricity'],
            ['name' => 'Yola Electric - YEDC', 'service_type' => 'electricity'],
            ['name' => 'Aba Electric - APLE', 'service_type' => 'electricity'],
            // Exam
            ['name' => 'WAEC registration PIN', 'service_type' => 'exam'],
            ['name' => 'WAEC checker PIN', 'service_type' => 'exam'],
            ['name' => 'UTME', 'service_type' => 'exam'],
            ['name' => 'Direct Entry (DE)', 'service_type' => 'exam'],
            ['name' => 'NECO e-VERIFICATION PIN', 'service_type' => 'exam'],
        ];

        $stmt = $pdo->prepare("INSERT INTO service_providers (name, service_type) VALUES (?, ?)");
        foreach ($providers as $provider) {
            $stmt->execute([$provider['name'], $provider['service_type']]);
        }

        // Seed Service Products
        $products = [
            // DSTV
            ['service_type' => 'cabletv', 'name' => 'DStv Padi', 'plan_code' => 'dstv-padi', 'amount' => 4400],
            ['service_type' => 'cabletv', 'name' => 'DStv Yanga', 'plan_code' => 'dstv-yanga', 'amount' => 6000],
            ['service_type' => 'cabletv', 'name' => 'Dstv Confam', 'plan_code' => 'dstv-confam', 'amount' => 11000],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact', 'plan_code' => 'dstv79', 'amount' => 19000],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium', 'plan_code' => 'dstv3', 'amount' => 44500],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact Plus', 'plan_code' => 'dstv7', 'amount' => 30000],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium-French', 'plan_code' => 'dstv9', 'amount' => 69000],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium-Asia', 'plan_code' => 'dstv10', 'amount' => 50500],
            ['service_type' => 'cabletv', 'name' => 'DStv Confam + ExtraView', 'plan_code' => 'confam-extra', 'amount' => 17000],
            ['service_type' => 'cabletv', 'name' => 'DStv Yanga + ExtraView', 'plan_code' => 'yanga-extra', 'amount' => 12000],
            ['service_type' => 'cabletv', 'name' => 'DStv Padi + ExtraView', 'plan_code' => 'padi-extra', 'amount' => 10400],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact + Extra View', 'plan_code' => 'dstv30', 'amount' => 25000],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact + French Touch', 'plan_code' => 'com-frenchtouch', 'amount' => 26000],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium + Extra View', 'plan_code' => 'dstv33', 'amount' => 50500],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact + French Touch + ExtraView', 'plan_code' => 'com-frenchtouch-extra', 'amount' => 32000],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact Plus + French Plus', 'plan_code' => 'dstv43', 'amount' => 54500],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact Plus + French Touch', 'plan_code' => 'complus-frenchtouch', 'amount' => 37000],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact Plus + Extra View', 'plan_code' => 'dstv45', 'amount' => 36000],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact Plus + FrenchPlus + Extra View', 'plan_code' => 'complus-french-extraview', 'amount' => 60500],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact + French Plus', 'plan_code' => 'dstv47', 'amount' => 43500],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium + French + Extra View', 'plan_code' => 'dstv62', 'amount' => 75000],
            ['service_type' => 'cabletv', 'name' => 'DStv French Plus Add-on', 'plan_code' => 'frenchplus-addon', 'amount' => 24500],
            ['service_type' => 'cabletv', 'name' => 'DStv Great Wall Standalone Bouquet', 'plan_code' => 'dstv-greatwall', 'amount' => 3800],
            ['service_type' => 'cabletv', 'name' => 'DStv French Touch Add-on', 'plan_code' => 'frenchtouch-addon', 'amount' => 7000],
            ['service_type' => 'cabletv', 'name' => 'ExtraView Access', 'plan_code' => 'extraview-access', 'amount' => 6000],
            ['service_type' => 'cabletv', 'name' => 'DStv Yanga + Showmax', 'plan_code' => 'dstv-yanga-showmax', 'amount' => 7750],
            ['service_type' => 'cabletv', 'name' => 'DStv Great Wall Standalone Bouquet + Showmax', 'plan_code' => 'dstv-greatwall-showmax', 'amount' => 7300],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact Plus + Showmax', 'plan_code' => 'dstv-compact-plus-showmax', 'amount' => 31750],
            ['service_type' => 'cabletv', 'name' => 'Dstv Confam + Showmax', 'plan_code' => 'dstv-confam-showmax', 'amount' => 12750],
            ['service_type' => 'cabletv', 'name' => 'DStv Compact + Showmax', 'plan_code' => 'dstv-compact-showmax', 'amount' => 20750],
            ['service_type' => 'cabletv', 'name' => 'DStv Padi + Showmax', 'plan_code' => 'dstv-padi-showmax', 'amount' => 7900],
            ['service_type' => 'cabletv', 'name' => 'DStv Asia + Showmax', 'plan_code' => 'dstv-asia-showmax', 'amount' => 18400],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium + French + Showmax', 'plan_code' => 'dstv-premium-french-showmax', 'amount' => 69000],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium + Showmax', 'plan_code' => 'dstv-premium-showmax', 'amount' => 44500],
            ['service_type' => 'cabletv', 'name' => 'DStv Indian', 'plan_code' => 'dstv-indian', 'amount' => 14900],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium East Africa and Indian', 'plan_code' => 'dstv-premium-indian', 'amount' => 16530],
            ['service_type' => 'cabletv', 'name' => 'DStv FTA Plus', 'plan_code' => 'dstv-fta-plus', 'amount' => 1600],
            ['service_type' => 'cabletv', 'name' => 'DStv PREMIUM HD', 'plan_code' => 'dstv-premium-hd', 'amount' => 39000],
            ['service_type' => 'cabletv', 'name' => 'DStv Access', 'plan_code' => 'dstv-access-1', 'amount' => 2000],
            ['service_type' => 'cabletv', 'name' => 'DStv Family', 'plan_code' => 'dstv-family-1', 'amount' => null],
            ['service_type' => 'cabletv', 'name' => 'DStv India Add-on', 'plan_code' => 'dstv-indian-add-on', 'amount' => 14900],
            ['service_type' => 'cabletv', 'name' => 'DSTV MOBILE', 'plan_code' => 'dstv-mobile-1', 'amount' => 790],
            ['service_type' => 'cabletv', 'name' => 'DStv Movie Bundle Add-on', 'plan_code' => 'dstv-movie-bundle-add-on', 'amount' => 3500],
            ['service_type' => 'cabletv', 'name' => 'DStv PVR Access Service', 'plan_code' => 'dstv-pvr-access', 'amount' => 4000],
            ['service_type' => 'cabletv', 'name' => 'DStv Premium W/Afr + Showmax', 'plan_code' => 'dstv-premium-wafr-showmax', 'amount' => 50500],
            // GOTV
            ['service_type' => 'cabletv', 'name' => 'GOtv Max', 'plan_code' => 'gotv-max', 'amount' => 8500],
            ['service_type' => 'cabletv', 'name' => 'GOtv Jolli', 'plan_code' => 'gotv-jolli', 'amount' => 5800],
            ['service_type' => 'cabletv', 'name' => 'GOtv Jinja', 'plan_code' => 'gotv-jinja', 'amount' => 3900],
            ['service_type' => 'cabletv', 'name' => 'GOtv Smallie - monthly', 'plan_code' => 'gotv-smallie', 'amount' => 1900],
            ['service_type' => 'cabletv', 'name' => 'GOtv Smallie - quarterly', 'plan_code' => 'gotv-smallie-3months', 'amount' => 5100],
            ['service_type' => 'cabletv', 'name' => 'GOtv Smallie - yearly', 'plan_code' => 'gotv-smallie-1year', 'amount' => 15000],
            ['service_type' => 'cabletv', 'name' => 'GOtv Supa - monthly', 'plan_code' => 'gotv-supa', 'amount' => 11400],
            ['service_type' => 'cabletv', 'name' => 'GOtv Supa Plus - monthly', 'plan_code' => 'gotv-supa-plus', 'amount' => 16800],
            // Startimes
            ['service_type' => 'cabletv', 'name' => 'Nova (Dish) - 1 Month', 'plan_code' => 'nova', 'amount' => 2100],
            ['service_type' => 'cabletv', 'name' => 'Basic (Antenna) - 1 Month', 'plan_code' => 'basic', 'amount' => 4000],
            ['service_type' => 'cabletv', 'name' => 'Basic (Dish) - 1 Month', 'plan_code' => 'smart', 'amount' => 5100],
            ['service_type' => 'cabletv', 'name' => 'Classic (Antenna) - 1 Month', 'plan_code' => 'classic', 'amount' => 6000],
            ['service_type' => 'cabletv', 'name' => 'Super (Dish) - 1 Month', 'plan_code' => 'super', 'amount' => 9800],
            ['service_type' => 'cabletv', 'name' => 'Nova (Antenna) - 1 Week', 'plan_code' => 'nova-weekly', 'amount' => 700],
            ['service_type' => 'cabletv', 'name' => 'Basic (Antenna) - 1 Week', 'plan_code' => 'basic-weekly', 'amount' => 1400],
            ['service_type' => 'cabletv', 'name' => 'Basic (Dish) - 1 Week', 'plan_code' => 'smart-weekly', 'amount' => 1700],
            ['service_type' => 'cabletv', 'name' => 'Classic (Antenna) - 1 Week', 'plan_code' => 'classic-weekly', 'amount' => 2000],
            ['service_type' => 'cabletv', 'name' => 'Super (Dish) - 1 Week', 'plan_code' => 'super-weekly', 'amount' => 3300],
            ['service_type' => 'cabletv', 'name' => 'Chinese (Dish) - 1 month', 'plan_code' => 'uni-1', 'amount' => 21000],
            ['service_type' => 'cabletv', 'name' => 'Nova (Antenna) - 1 Month', 'plan_code' => 'uni-2', 'amount' => 2100],
            ['service_type' => 'cabletv', 'name' => 'Classic (Dish) - 1 Week', 'plan_code' => 'special-weekly', 'amount' => 2300],
            ['service_type' => 'cabletv', 'name' => 'Classic (Dish) - 1 Month', 'plan_code' => 'special-monthly', 'amount' => 7400],
            ['service_type' => 'cabletv', 'name' => 'Nova (Dish) - 1 Week', 'plan_code' => 'nova-dish-weekly', 'amount' => 700],
            ['service_type' => 'cabletv', 'name' => 'Super (Antenna) - 1 Week', 'plan_code' => 'super-antenna-weekly', 'amount' => 3200],
            ['service_type' => 'cabletv', 'name' => 'Super (Antenna) - 1 Month', 'plan_code' => 'super-antenna-monthly', 'amount' => 9500],
            ['service_type' => 'cabletv', 'name' => 'Classic (Dish) - 1 Week', 'plan_code' => 'classic-weekly-dish', 'amount' => 2500],
            ['service_type' => 'cabletv', 'name' => 'Global (Dish) - 1 Month', 'plan_code' => 'global-monthly-dish', 'amount' => 21000],
            ['service_type' => 'cabletv', 'name' => 'Global (Dish) - 1Week', 'plan_code' => 'global-weekly-dish', 'amount' => 7000],
            ['service_type' => 'cabletv', 'name' => 'Startimes SHS - Weekly', 'plan_code' => 'shs-weekly-2800', 'amount' => 2800],
            ['service_type' => 'cabletv', 'name' => 'Startimes SHS - Weekly', 'plan_code' => 'shs-weekly-4620', 'amount' => 4620],
            ['service_type' => 'cabletv', 'name' => 'Startimes SHS - Weekly', 'plan_code' => 'shs-weekly-4900', 'amount' => 4900],
            ['service_type' => 'cabletv', 'name' => 'Startimes SHS - Weekly', 'plan_code' => 'shs-weekly-9100', 'amount' => 9100],
            ['service_type' => 'cabletv', 'name' => 'Startimes SHS - Monthly', 'plan_code' => 'shs-monthly-12000', 'amount' => 12000],
            ['service_type' => 'cabletv', 'name' => 'Startimes SHS - Monthly', 'plan_code' => 'shs-monthly-19800', 'amount' => 19800],
            ['service_type' => 'cabletv', 'name' => 'Startimes SHS - Monthly', 'plan_code' => 'shs-monthly-21000', 'amount' => 21000],
            ['service_type' => 'cabletv', 'name' => 'Startimes SHS - Monthly', 'plan_code' => 'shs-monthly-39000', 'amount' => 39000],
            // ELECTRICITY
            ['service_type' => 'electricity', 'name' => 'Prepaid', 'plan_code' => '01', 'amount' => null],
            ['service_type' => 'electricity', 'name' => 'Postpaid', 'plan_code' => '02', 'amount' => null],
            // EXAM
            ['service_type' => 'exam', 'name' => 'WAEC registration PIN', 'plan_code' => 'waec-registration', 'amount' => null],
            ['service_type' => 'exam', 'name' => 'WAEC checker PIN', 'plan_code' => 'waec', 'amount' => null],
            ['service_type' => 'exam', 'name' => 'UTME', 'plan_code' => 'utme', 'amount' => null],
            ['service_type' => 'exam', 'name' => 'Direct Entry (DE)', 'plan_code' => 'de', 'amount' => null],
            ['service_type' => 'exam', 'name' => 'NECO e-VERIFICATION PIN', 'plan_code' => 'neco', 'amount' => null],
        ];

        $stmt = $pdo->prepare("INSERT INTO service_products (service_type, name, plan_code, amount) VALUES (?, ?, ?, ?)");
        foreach ($products as $product) {
            $stmt->execute([$product['service_type'], $product['name'], $product['plan_code'], $product['amount']]);
        }
    },
    "down" => function($pdo) {
        // This can be used to reverse the migration, e.g., for testing or rollback.
        $pdo->exec("DELETE FROM service_products;");
        $pdo->exec("DELETE FROM service_providers;");
        $pdo->exec("DELETE FROM networks;");
    }
];
