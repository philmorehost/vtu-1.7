<?php
/**
 * Test script for Modular API Provider System
 * Run this to verify the system is working correctly
 */

require_once('apis/ApiProviderRegistry.php');

echo "=== Modular API Provider System Test ===\n\n";

try {
    // Test 1: Provider Registry
    echo "1. Testing Provider Registry...\n";
    $providers = ApiProviderRegistry::getProviders();
    echo "   Found " . count($providers) . " providers:\n";
    foreach ($providers as $name => $info) {
        echo "   - $name: {$info['class']}\n";
    }
    echo "   ✓ Provider Registry working\n\n";

    // Test 2: Provider Instantiation
    echo "2. Testing Provider Instantiation...\n";
    foreach (array_keys($providers) as $providerName) {
        try {
            $provider = ApiProviderRegistry::getProvider($providerName, [
                'api_key' => 'test_key',
                'secret_key' => 'test_secret'
            ]);
            $info = $provider->getProviderInfo();
            echo "   - {$info['display_name']}: ✓\n";
            echo "     Services: " . implode(', ', $provider->getSupportedServices()) . "\n";
            echo "     Required Config: " . implode(', ', $provider->getRequiredConfig()) . "\n";
        } catch (Exception $e) {
            echo "   - $providerName: ✗ (" . $e->getMessage() . ")\n";
        }
    }
    echo "   ✓ Provider instantiation working\n\n";

    // Test 3: Provider List for Dropdown
    echo "3. Testing Provider List for Dropdown...\n";
    $providerList = ApiProviderRegistry::getProviderList();
    foreach ($providerList as $key => $name) {
        echo "   - $key: $name\n";
    }
    echo "   ✓ Provider list generation working\n\n";

    // Test 4: Configuration Validation
    echo "4. Testing Configuration Validation...\n";
    $provider = ApiProviderRegistry::getProvider('clubkonnect', [
        'api_key' => 'test_key',
        'secret_key' => 'test_secret'
    ]);
    
    try {
        $provider->validateConfig();
        echo "   ✓ Configuration validation working\n\n";
    } catch (Exception $e) {
        echo "   ✗ Configuration validation failed: " . $e->getMessage() . "\n\n";
    }

    echo "=== All Tests Completed Successfully! ===\n";
    echo "\nNext Steps:\n";
    echo "1. Run 'php migrate_modular_api.php' to set up the database\n";
    echo "2. Access admin/apimanager/modular_manager.php to configure providers\n";
    echo "3. Test the new API endpoints (airtime_modular.php, data_modular.php)\n";

} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    echo "Please check your installation and try again.\n";
}
?>