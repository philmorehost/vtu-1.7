<?php
/**
 * API Provider Registry
 * Manages available API provider modules
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class ApiProviderRegistry {
    private static $providers = [];
    
    /**
     * Register an API provider
     */
    public static function register($name, $className, $filePath) {
        self::$providers[$name] = [
            'class' => $className,
            'file' => $filePath
        ];
    }
    
    /**
     * Get all registered providers
     */
    public static function getProviders() {
        return self::$providers;
    }
    
    /**
     * Get provider instance
     */
    public static function getProvider($name, $config = []) {
        if (!isset(self::$providers[$name])) {
            throw new Exception("Provider '{$name}' not found");
        }
        
        $providerInfo = self::$providers[$name];
        
        // Load the provider class if not already loaded
        if (!class_exists($providerInfo['class'])) {
            if (file_exists($providerInfo['file'])) {
                require_once($providerInfo['file']);
            } else {
                throw new Exception("Provider file not found: {$providerInfo['file']}");
            }
        }
        
        return new $providerInfo['class']($config);
    }
    
    /**
     * Get provider list for dropdown
     */
    public static function getProviderList() {
        $list = [];
        foreach (self::$providers as $name => $info) {
            try {
                $instance = self::getProvider($name);
                $providerInfo = $instance->getProviderInfo();
                $list[$name] = $providerInfo['display_name'];
            } catch (Exception $e) {
                // Skip providers that can't be instantiated
                continue;
            }
        }
        return $list;
    }
    
    /**
     * Auto-discover and register providers
     */
    public static function autoRegister($directory = null) {
        if ($directory === null) {
            $directory = __DIR__;
        }
        
        $files = glob($directory . '/*Provider.php');
        foreach ($files as $file) {
            $className = basename($file, '.php');
            if ($className !== 'BaseApiProvider') {
                $name = strtolower(str_replace('Provider', '', $className));
                self::register($name, $className, $file);
            }
        }
    }
}

// Auto-register providers on include
ApiProviderRegistry::autoRegister();