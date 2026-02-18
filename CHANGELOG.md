# Changelog

All notable changes to the SiAntre (Sistem Antrian Online) project will be documented in this file.

## [MVP 1.0] - 2026-01-29

### Added
- Complete queue management system with real-time updates
- Multi-role system (Admin, Operator, Patient) with role-based access control
- Service management with unique prefixes and daily limits
- Queue status tracking with waiting, called, done, and canceled states
- Public display screen with live queue information
- QR code integration for easy patient access
- Daily queue reset functionality at midnight
- Comprehensive logging and monitoring system
- Soft deletes implementation for data preservation
- Caching strategies for improved performance
- Rate limiting to prevent spam queue generation
- Validated status transitions for queue management
- Estimated wait time calculation
- Position in queue tracking
- User activity logging
- Performance metrics logging
- Two-Factor Authentication (2FA) for admin/operator accounts
- IP Whitelisting with database-driven configuration
- Granular permissions system with role-based permissions
- Session timeout configuration per user role
- Security headers implementation
- UI-based IP whitelist management interface

### Changed
- Enhanced User model with soft deletes, validation rules, and helper methods
- Enhanced Service model with soft deletes, validation rules, and helper methods
- Enhanced Queue model with soft deletes, validation rules, and business logic
- Improved error handling throughout all controllers
- Enhanced security with comprehensive logging and audit trails
- Improved validation and sanitization of inputs
- Enhanced views with better structure and maintainability
- Improved middleware with comprehensive logging
- Enhanced QueueMonitoringService with additional functionality
- Improved caching strategies for better performance
- Enhanced authentication and authorization system
- Improved database schema with proper constraints
- Added proper cache invalidation for real-time service updates
- Fixed operator dashboard to show all services correctly
- Improved queue generation sequence logic
- Enhanced operator interface with better security, validation and error handling
- Improved operator queue management functionality
- Added comprehensive error handling for operator actions
- Integrated spatie/laravel-permission package for granular permissions
- Added Google2FA integration for two-factor authentication
- Implemented database-driven IP whitelisting system
- Added role-based session timeout middleware
- Enhanced security headers middleware

### Fixed
- Race condition prevention in queue number generation
- Unique constraint implementation for queue numbers per day
- Proper transaction handling in queue operations
- Security vulnerabilities in role-based access control
- Performance issues with database queries
- Error handling in all controller methods
- Validation issues in form submissions
- Session management improvements
- CSRF protection enhancements
- Cache invalidation issues for real-time service updates
- Operator dashboard showing only one service issue
- Queue generation sequence issues
- 2FA implementation issues
- IP whitelisting configuration problems
- Permission system integration issues

### Security
- Implemented role-based access control (RBAC)
- Added comprehensive audit logging
- Enhanced input validation and sanitization
- Implemented soft deletes for data preservation
- Added user activity tracking
- Improved session security
- Enhanced password hashing and security
- Added Two-Factor Authentication (2FA) for admin/operator accounts
- Implemented IP whitelisting with database-driven configuration
- Added granular permissions system with role-based permissions
- Implemented role-based session timeout configuration
- Added security headers for enhanced protection
- Created UI-based IP whitelist management interface

## [Initial Development] - 2026-01-28

### Added
- Initial Laravel 11 project setup
- Basic authentication with Laravel Breeze
- User, Service, and Queue models
- Admin, Operator, and Patient controllers
- Basic views with Tailwind CSS
- Database migrations and seeders
- Basic queue generation functionality
- Initial testing suite