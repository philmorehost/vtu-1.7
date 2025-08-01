# API Gateway and Dynamic Service Integration - Documentation

## Overview
This system implements a comprehensive API Gateway and Dynamic Service Integration for the VTU Template platform. It replaces hardcoded service configurations with a flexible, database-driven approach that supports multiple API providers and dynamic service management.

## Key Features

### 1. Database Schema
The system introduces 5 new database tables:
- **`api_providers`**: Stores API provider configurations (credentials, endpoints, priorities)
- **`networks`**: Manages telecom networks (MTN, GLO, Airtel, 9mobile)
- **`service_products`**: All service products with pricing and configurations
- **`api_routes`**: Maps services to API providers with request/response mappings
- **`product_networks`**: Many-to-many relationship for products and networks

### 2. Admin Interface

#### API Gateway Manager (`admin/apimanager/api_gateway_manager.php`)
- **Providers Tab**: Add, edit, delete API providers with authentication settings
- **Routes Tab**: Configure service routing with request/response mappings
- **Networks Tab**: View and manage telecom networks
- Features priority-based routing, status management, and test mode support

#### Enhanced Services Management (`admin/services.php`)
- Dynamic service product management with database integration
- Grid/List view toggle for better UX
- Support for all service types (data, airtime, cable TV, electricity, etc.)
- Price/discount management with real-time updates

### 3. API Gateway System

#### ApiGateway Class (`includes/ApiGateway.php`)
Core functionality includes:
- **Dynamic Routing**: Routes requests to appropriate API providers based on priority
- **Request/Response Mapping**: JSON-based parameter mapping for different APIs
- **Authentication Support**: Bearer, Basic Auth, API Key, and custom authentication
- **Transaction Management**: Handles pending/completed/failed transaction states
- **Requery Logic**: Re-check transaction status for failed/pending transactions
- **Error Handling**: Comprehensive error logging and fallback mechanisms

#### API Endpoints
**Data API (`api/data.php`)**:
- Dynamic plan loading from database
- Network detection and routing
- Transaction status management
- Requery support for failed transactions

**Airtime API (`api/airtime.php`)**:
- Network-specific discount application
- Dynamic routing based on network
- Real-time balance validation
- API provider failover support

**Services API (`api/services.php`)**:
- Provides dynamic data for frontend AJAX calls
- Network detection endpoint
- Service data grouped by type and network
- Real-time service availability

### 4. Frontend Integration

#### Dynamic Service Loading (`assets/js/main.js`)
- AJAX-based service data loading
- Dynamic population of dropdowns and forms
- Real-time network detection
- Service availability indicators
- Async/await pattern for better performance

## Usage Instructions

### Setting Up API Providers
1. Navigate to **Admin > API Manager**
2. Click "Add Provider" to configure a new API provider
3. Fill in provider details:
   - **Name**: Internal identifier (e.g., 'club_konnect')
   - **Display Name**: Human-readable name
   - **Base URL**: API base endpoint
   - **Authentication**: Choose auth type and provide credentials
   - **Priority**: Higher numbers get priority in routing

### Configuring API Routes
1. Go to **API Routes** tab in API Manager
2. Click "Add Route" to map a service to an API provider
3. Configure:
   - **Service Type**: data, airtime, cabletv, etc.
   - **Network**: Specific network or "All Networks"
   - **Endpoint**: API endpoint path
   - **Request Mapping**: JSON mapping for API parameters
   - **Response Mapping**: JSON mapping for API responses

#### Example Request Mapping:
```json
{
  "phone": "{phoneNumber}",
  "plan": "{planId}",
  "amount": "{amount}"
}
```

#### Example Response Mapping:
```json
{
  "success": "status",
  "message": "description",
  "reference": "transaction_ref"
}
```

### Managing Service Products
1. Go to **Admin > Services**
2. Use tabs to navigate between service types
3. Toggle between Grid and List view using the view selector
4. Add new products with:
   - Service type and network association
   - Cost price and selling price
   - Discount percentages
   - Service-specific data (data size, validity, etc.)

### API Request Flow
1. **Frontend Request**: User initiates service request
2. **Service Detection**: System identifies service type and network
3. **Route Selection**: API Gateway selects best available route
4. **Request Mapping**: Parameters mapped according to route configuration
5. **API Call**: Request sent to provider with proper authentication
6. **Response Processing**: Response mapped and parsed
7. **Transaction Update**: Database updated with final status
8. **User Feedback**: Result returned to frontend

### Error Handling
- **Provider Failover**: Automatic fallback to next priority provider
- **Transaction Requery**: Automatic retry for pending transactions
- **Logging**: Comprehensive error logging for debugging
- **User Notifications**: Clear error messages for end users

### Database Migration
Run the migration script to set up the new database schema:
```bash
php migrate_api_gateway.php
```

This will:
- Create all required tables
- Insert default networks (MTN, GLO, Airtel, 9mobile)
- Add sample API provider
- Migrate existing hardcoded data

## Security Considerations
- API credentials stored securely in database
- Transaction validation with balance locking
- SQL injection protection with prepared statements
- XSS protection with output escaping
- Rate limiting support in API Gateway

## Performance Features
- Efficient database queries with proper indexing
- Caching support for frequently accessed data
- Async JavaScript for non-blocking operations
- Connection pooling for API requests
- Transaction batching for bulk operations

## Monitoring and Debugging
- Comprehensive error logging in PHP error logs
- Transaction status tracking in database
- API response logging for debugging
- Performance metrics via database queries
- Admin interface for real-time monitoring

## Future Enhancements
- API rate limiting implementation
- Real-time balance checking
- Webhook support for transaction callbacks
- Multi-currency support
- Advanced reporting and analytics
- API versioning and backward compatibility

## Support
For issues or questions regarding the API Gateway system:
1. Check error logs for detailed error messages
2. Verify API provider configurations in Admin panel
3. Test API routes using the built-in test mode
4. Review transaction logs for failed operations
5. Contact system administrator for provider-specific issues