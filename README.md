# VTU Template - Enhanced with API Gateway

A comprehensive VTU (Virtual Top-Up) platform template with integrated API Gateway system for dynamic service management.

## 🚀 New Features (API Gateway Integration)

### Dynamic Service Management
- **Database-driven services**: No more hardcoded service configurations
- **Multiple API provider support**: Route services to different providers with priority-based selection
- **Real-time service availability**: Services are automatically enabled/disabled based on API provider status
- **Dynamic pricing**: Manage prices and discounts from admin interface without code changes

### Admin Interface Enhancements
- **API Gateway Manager**: Complete interface for managing API providers, routes, and configurations
- **Enhanced Services Management**: Grid/List view toggle with full CRUD operations for service products
- **Real-time Configuration**: Changes take effect immediately without requiring code deployment

### Frontend Improvements
- **AJAX-powered interface**: All service data loaded dynamically via REST API
- **Smart network detection**: Automatic network detection from phone numbers
- **Real-time availability**: Service buttons show availability status
- **Responsive design**: Enhanced UX with modern interface patterns

### API System
- **RESTful endpoints**: Clean API design with proper HTTP methods and status codes
- **Transaction management**: Proper pending/completed/failed transaction handling
- **Requery functionality**: Automatic retry for failed/pending transactions
- **Comprehensive logging**: Full request/response logging for debugging

## 📋 Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- cURL extension enabled

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/philmorehost/vtu-template.git
   cd vtu-template
   ```

2. **Set up the database**
   - Create a MySQL database
   - Update database credentials in `includes/config.php`
   - Run the setup script: `php setup.php`
   - Run the API Gateway migration: `php migrate_api_gateway.php`

3. **Configure web server**
   - Point document root to the project folder
   - Ensure mod_rewrite is enabled (for Apache)

4. **Initial setup**
   - Access the admin panel: `/admin/`
   - Default admin credentials: `admin@example.com` / `password`
   - Configure API providers in API Gateway Manager

## 📚 Documentation

### API Gateway System
See [API_GATEWAY_DOCUMENTATION.md](API_GATEWAY_DOCUMENTATION.md) for comprehensive documentation including:
- Setting up API providers
- Configuring service routes
- Request/response mapping
- Error handling and debugging
- Security considerations

### Database Schema
The system includes 5 new database tables:
- `api_providers`: API provider configurations
- `networks`: Telecom network definitions
- `service_products`: All service products with pricing
- `api_routes`: Service to API provider mappings
- `product_networks`: Many-to-many relationship table

## 🎯 Key Features

### For Administrators
- **Multi-provider management**: Support for multiple API providers with failover
- **Dynamic configuration**: Change prices, add services, configure APIs without code changes
- **Priority-based routing**: Route services to preferred providers
- **Real-time monitoring**: Transaction tracking and API response monitoring

### For End Users
- **Faster loading**: AJAX-based interface with improved performance
- **Better UX**: Real-time network detection and service availability
- **Reliable transactions**: Automatic retry for failed transactions
- **Comprehensive feedback**: Clear status messages and transaction tracking

### For Developers
- **Clean architecture**: Separation of concerns with dedicated API Gateway class
- **Extensible design**: Easy to add new service types and API providers
- **Comprehensive logging**: Full request/response logging for debugging
- **RESTful APIs**: Clean API design following REST principles

## 🔧 Configuration

### API Providers
Configure API providers through the admin interface:
1. Go to **Admin > API Manager**
2. Add provider details (name, URL, credentials)
3. Set authentication type and priority
4. Configure service routes for each provider

### Service Products
Manage service products dynamically:
1. Go to **Admin > Services**
2. Add/edit products for each service type
3. Set pricing and discounts
4. Configure network associations

## 🛡️ Security Features
- SQL injection protection with prepared statements
- XSS protection with output escaping
- Secure API credential storage
- Transaction validation with balance locking
- Comprehensive error handling

## 🚀 Performance Optimizations
- Efficient database queries with proper indexing
- AJAX-based frontend for faster interactions
- Connection pooling for API requests
- Caching support for frequently accessed data

## 📞 Support
For issues related to the API Gateway system:
1. Check the comprehensive documentation
2. Review error logs for detailed messages
3. Test API configurations using the admin interface
4. Verify database migrations have been run

## 🤝 Contributing
Contributions are welcome! Please read the documentation before making changes to the API Gateway system.

## 📄 License
This project is licensed under the MIT License - see the LICENSE file for details.

---

**Note**: This enhanced version includes a complete API Gateway system that replaces hardcoded configurations with dynamic, database-driven service management. See the documentation for detailed setup and usage instructions.