<?php
/**
 * System Switch Helper
 * Helps administrators transition from legacy to modular API system
 */

require_once('includes/config.php');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== VTU Platform API System Switch Helper ===\n\n";

    // Check current system status
    echo "1. Checking current system status...\n";
    
    // Check if modular tables exist
    $modularTablesExist = false;
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'api_provider_routes'");
        $modularTablesExist = $stmt->rowCount() > 0;
    } catch (Exception $e) {
        // Table doesn't exist
    }
    
    if ($modularTablesExist) {
        echo "   ✓ Modular system tables found\n";
        
        // Check for configured providers
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM api_providers WHERE provider_module IS NOT NULL");
        $modularProviders = $stmt->fetch()['count'];
        echo "   ✓ Found $modularProviders modular providers\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM api_provider_routes WHERE status = 'active'");
        $activeRoutes = $stmt->fetch()['count'];
        echo "   ✓ Found $activeRoutes active service routes\n";
    } else {
        echo "   ⚠ Modular system not yet installed\n";
        echo "   → Run 'php migrate_modular_api.php' to install\n";
    }
    
    // Check legacy system
    $legacyTablesExist = false;
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'api_routes'");
        $legacyTablesExist = $stmt->rowCount() > 0;
    } catch (Exception $e) {
        // Table doesn't exist
    }
    
    if ($legacyTablesExist) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM api_routes WHERE status = 'active'");
        $legacyRoutes = $stmt->fetch()['count'];
        echo "   ✓ Found $legacyRoutes legacy API routes\n";
    } else {
        echo "   ⚠ Legacy API routes table not found\n";
    }
    
    echo "\n2. System recommendations...\n";
    
    if (!$modularTablesExist) {
        echo "   🔧 SETUP REQUIRED:\n";
        echo "      1. Run: php migrate_modular_api.php\n";
        echo "      2. Configure providers in admin/apimanager/modular_manager.php\n";
        echo "      3. Test new endpoints (api/airtime_modular.php, api/data_modular.php)\n";
    } elseif ($modularProviders == 0) {
        echo "   ⚙️ CONFIGURATION NEEDED:\n";
        echo "      1. Go to admin/apimanager/modular_manager.php\n";
        echo "      2. Add and configure your API providers\n";
        echo "      3. Test the services\n";
    } else {
        echo "   ✅ SYSTEM READY:\n";
        echo "      • Modular system is installed and configured\n";
        echo "      • You can start using the new simplified interface\n";
        echo "      • Legacy system is still available for compatibility\n";
        
        if ($legacyRoutes > 0) {
            echo "   \n   📋 MIGRATION OPTIONS:\n";
            echo "      A. Gradual Migration (Recommended):\n";
            echo "         - Use both systems in parallel\n";
            echo "         - Test modular system thoroughly\n";
            echo "         - Switch services one by one\n";
            echo "      \n      B. Complete Switch:\n";
            echo "         - Update all service endpoints to use modular system\n";
            echo "         - Backup and disable legacy routes\n";
            echo "         - Monitor for any issues\n";
        }
    }
    
    echo "\n3. Quick actions...\n";
    
    if ($modularTablesExist && $modularProviders > 0) {
        echo "   Available endpoints:\n";
        echo "   • api/airtime_modular.php (Enhanced airtime)\n";
        echo "   • api/data_modular.php (Enhanced data)\n";
        echo "   • api/airtime.php (Legacy - still available)\n";
        echo "   • api/data.php (Legacy - still available)\n";
        
        echo "   \n   Admin interfaces:\n";
        echo "   • admin/apimanager/modular_manager.php (New simplified interface)\n";
        echo "   • admin/apimanager/api_gateway_manager.php (Legacy interface)\n";
    }
    
    echo "\n4. File structure overview...\n";
    echo "   New modular files:\n";
    if (file_exists('apis/BaseApiProvider.php')) echo "   ✓ apis/BaseApiProvider.php\n";
    if (file_exists('apis/ApiProviderRegistry.php')) echo "   ✓ apis/ApiProviderRegistry.php\n";
    if (file_exists('apis/ClubkonnectProvider.php')) echo "   ✓ apis/ClubkonnectProvider.php\n";
    if (file_exists('apis/SmartdataProvider.php')) echo "   ✓ apis/SmartdataProvider.php\n";
    if (file_exists('apis/VtpassProvider.php')) echo "   ✓ apis/VtpassProvider.php\n";
    if (file_exists('includes/ModularApiGateway.php')) echo "   ✓ includes/ModularApiGateway.php\n";
    
    echo "\n=== Switch Helper Complete ===\n";
    
    if ($modularTablesExist && $modularProviders > 0) {
        echo "\n🎉 Your modular API system is ready to use!\n";
        echo "Access the new admin interface at: admin/apimanager/modular_manager.php\n";
    } else {
        echo "\n⚠️ Setup required before you can use the modular system.\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration.\n";
}
?>