# Modular API Provider System Documentation

## Overview

The VTU platform has been refactored to use a modular API provider system that eliminates the need for complex JSON request/response mappings. Instead of manually configuring API routes with JSON templates, administrators can now simply select pre-integrated API providers from a dropdown and configure them with basic credentials.

## Key Features

### 1. **Pre-Integrated API Providers**
- **ClubKonnect Provider**: Full support for airtime, data, cable TV, and electricity
- **Smartdata Provider**: Comprehensive VTU services with exam pin support
- **VTPass Provider**: Popular bills payment platform integration

### 2. **Simplified Admin Interface**
- Dropdown selection of API providers
- Simple API key/secret configuration
- Service selection checkboxes
- Priority and status management
- One-click provider testing

### 3. **Extensible Architecture**
- Easy addition of new providers
- Standardized interface for all services
- Automatic provider discovery
- Backward compatibility with existing system

## Directory Structure

```
├── apis/
│   ├── BaseApiProvider.php         # Abstract base class
│   ├── ApiProviderRegistry.php     # Provider management
│   ├── ClubkonnectProvider.php     # ClubKonnect integration
│   ├── SmartdataProvider.php       # Smartdata integration
│   ├── VtpassProvider.php          # VTPass integration
│   └── [OtherProvider].php         # Additional providers
├── includes/
│   ├── ModularApiGateway.php       # Enhanced gateway class
│   └── ApiGateway.php              # Legacy gateway (kept for compatibility)
├── admin/apimanager/
│   ├── modular_manager.php         # New simplified admin interface
│   └── api_gateway_manager.php     # Legacy interface (kept for reference)
├── api/
│   ├── airtime_modular.php         # Enhanced airtime endpoint
│   ├── data_modular.php            # Enhanced data endpoint
│   ├── airtime.php                 # Legacy endpoint (kept for compatibility)
│   └── data.php                    # Legacy endpoint (kept for compatibility)
└── migrate_modular_api.php         # Database migration script
```

## Database Schema Changes

### New Tables

#### `api_provider_routes`
Simplified routing table replacing complex `api_routes`:
```sql
CREATE TABLE `api_provider_routes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `api_provider_id` INT NOT NULL,
    `service_type` ENUM('airtime', 'data', 'cable_tv', 'electricity', 'exam', 'betting', 'recharge_card', 'bulk_sms', 'gift_card') NOT NULL,
    `network_id` INT NULL,
    `priority` INT DEFAULT 1,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    -- Foreign keys and indexes
);
```

#### `api_transaction_logs`
Enhanced transaction logging:
```sql
CREATE TABLE `api_transaction_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `service_type` VARCHAR(50) NOT NULL,
    `provider_id` INT NOT NULL,
    `success` BOOLEAN NOT NULL,
    `response_message` TEXT,
    `request_data` TEXT,
    `response_data` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Updated Tables

#### `api_providers`
Added fields for modular system:
```sql
ALTER TABLE api_providers 
ADD COLUMN provider_module VARCHAR(100) DEFAULT NULL,
ADD COLUMN config_fields TEXT DEFAULT NULL;
```

## Creating New API Providers

### Step 1: Create Provider Class

```php
<?php
require_once(__DIR__ . '/BaseApiProvider.php');

class YourProviderProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'yourprovider',
            'display_name' => 'Your Provider',
            'description' => 'Your provider description',
            'website' => 'https://yourprovider.com/',
            'logo' => '/assets/images/providers/yourprovider.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://api.yourprovider.com/';
    }
    
    public function getSupportedServices() {
        return ['airtime', 'data', 'cable_tv'];
    }
    
    public function getRequiredConfig() {
        return ['api_key', 'secret_key'];
    }
    
    protected function getAuthHeaders() {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'X-Secret-Key: ' . $this->secretKey
        ];
    }
    
    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        $this->validateConfig();
        
        $data = [
            'phone' => $phoneNumber,
            'amount' => $amount,
            'network' => $network
        ];
        
        try {
            $result = $this->makeRequest('airtime', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        'Airtime purchase successful',
                        $response,
                        $response['transaction_id'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['message'] ?? 'Airtime purchase failed',
                        $response
                    );
                }
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    // Implement other service methods as needed
}
```

### Step 2: Auto-Registration

The provider will be automatically discovered and registered when placed in the `apis/` directory with the filename pattern `*Provider.php`.

### Step 3: Admin Configuration

1. Go to **Admin Panel → API Manager → Modular Manager**
2. Click **Add API Provider**
3. Select your provider from the dropdown
4. Enter API credentials
5. Select supported services
6. Set priority and status
7. Save

## Migration Guide

### Running the Migration

1. **Backup your database** (recommended)
2. Run the migration script:
   ```bash
   php migrate_modular_api.php
   ```
3. Verify the migration completed successfully
4. Configure your API providers in the new interface

### Updating Service Endpoints

Replace old endpoints with modular versions:

#### Before (Legacy)
```php
$apiGateway = new ApiGateway($pdo);
$route = $apiGateway->getRoute('airtime', $networkId);
$apiResponse = $apiGateway->makeApiRequest($route, $requestData);
```

#### After (Modular)
```php
$modularGateway = new ModularApiGateway($pdo);
$apiResponse = $modularGateway->purchaseAirtime($phoneNumber, $amount, $network);
```

## Admin Interface Changes

### Old Interface (Complex)
- Required JSON request/response mapping knowledge
- Manual endpoint configuration
- Complex authentication setup
- Technical expertise needed

### New Interface (Simplified)
- Dropdown provider selection
- Simple API key/secret input
- Checkbox service selection
- One-click testing
- No technical knowledge required

## Benefits

### For Administrators
- **Simplified Setup**: No more JSON mappings
- **Faster Configuration**: Minutes instead of hours
- **Reduced Errors**: Pre-tested integrations
- **Easy Testing**: Built-in provider testing

### For Developers
- **Cleaner Code**: Object-oriented provider classes
- **Better Maintainability**: Standardized interfaces
- **Easier Debugging**: Comprehensive logging
- **Extensibility**: Simple to add new providers

### For the Platform
- **Reliability**: Pre-tested provider integrations
- **Scalability**: Easy to add new services and providers
- **Monitoring**: Enhanced transaction logging
- **Backward Compatibility**: Existing functionality preserved

## Testing

### Provider Testing
```php
// Test provider connectivity and credentials
$result = $provider->checkBalance();

// Test specific service
$result = $provider->purchaseAirtime('08012345678', 100, 'MTN');
```

### Admin Interface Testing
1. Add a test provider with dummy credentials
2. Try all supported services
3. Verify error handling
4. Check transaction logging

## Troubleshooting

### Common Issues

#### Provider Not Found
- Ensure provider file is in `apis/` directory
- Check filename follows `*Provider.php` pattern
- Verify class extends `BaseApiProvider`

#### Configuration Errors
- Check required configuration fields
- Verify API credentials are correct
- Test provider connectivity

#### Service Failures
- Check transaction logs in `api_transaction_logs` table
- Verify provider supports the requested service
- Check network/service availability

### Logging

All API interactions are logged in:
- **Error Log**: `/var/log/apache2/error.log` (or server-specific)
- **Database**: `api_transaction_logs` table
- **File Log**: Custom logging can be added to providers

## Security Considerations

### API Credentials
- Stored encrypted in database (recommended)
- Never expose in client-side code
- Use environment variables for sensitive data

### Request Validation
- Input sanitization on all endpoints
- Rate limiting implemented
- Admin controls for blocking and limits

### Error Handling
- No sensitive information in error messages
- Comprehensive logging for debugging
- Graceful degradation on failures

## Performance Optimization

### Caching
- Provider configurations cached
- Network detection results cached
- Service product data cached

### Connection Pooling
- Reuse HTTP connections where possible
- Implement connection timeouts
- Handle provider rate limits

### Database Optimization
- Indexed tables for fast lookups
- Optimized transaction logging
- Batch operations where applicable

## Future Enhancements

### Planned Features
- **Provider Monitoring**: Real-time status monitoring
- **Load Balancing**: Automatic provider failover
- **Rate Limiting**: Per-provider rate limiting
- **Analytics**: Provider performance analytics
- **WebHooks**: Real-time transaction notifications

### Extension Points
- Custom authentication methods
- Provider-specific configurations
- Dynamic service discovery
- Multi-region support

## Support

For technical support or questions about the modular API system:

1. Check the error logs for specific error messages
2. Review the provider implementation for custom providers
3. Verify database migration completed successfully
4. Test with a simple provider like ClubKonnect first

## Conclusion

The modular API provider system significantly simplifies API management while maintaining full functionality and adding extensibility. The pre-integrated providers handle all the complex mappings internally, allowing administrators to focus on configuration rather than technical implementation details.