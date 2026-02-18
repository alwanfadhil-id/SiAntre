# SiAntre (Sistem Antrian Online) - Documentation

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Architecture](#architecture)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Usage Guide](#usage-guide)
7. [API Endpoints](#api-endpoints)
8. [Security Features](#security-features)
9. [Performance Optimizations](#performance-optimizations)
10. [Troubleshooting](#troubleshooting)

## Overview

SiAntre (Sistem Antrian Online) is a comprehensive online queue management system designed for clinics, workshops, salons, and village offices. The system provides a web-based solution for managing queues with real-time updates and role-based access control.

## Features

### Core Features
- **Multi-role System**: Admin, Operator, and Patient roles with specific permissions
- **Queue Management**: Real-time queue generation and status tracking
- **Service Management**: Multiple service types with unique prefixes
- **Real-time Display**: Public display screen with live queue information
- **QR Code Integration**: Easy patient access via QR codes
- **Daily Queue Reset**: Automatic reset of queues at midnight
- **Comprehensive Logging**: Full audit trail of all operations

### Advanced Features
- **Soft Deletes**: Data preservation with soft delete functionality
- **Caching System**: Performance optimization with strategic caching
- **Rate Limiting**: Protection against spam queue generation
- **Anti-Duplication**: Prevention of multiple queue generation per user per service per day using IP-based validation
- **Status Transitions**: Validated queue status changes
- **Security Monitoring**: Comprehensive security and audit logging
- **Real-time Updates**: Proper cache invalidation for real-time service updates
- **Operator Dashboard**: Shows all services with current queue status

## Architecture

### Technology Stack
- **Framework**: Laravel 11
- **Database**: MySQL
- **Frontend**: Tailwind CSS, Alpine.js
- **Authentication**: Laravel Breeze
- **Queue System**: Laravel Queues
- **Testing**: PHPUnit

### Database Schema
- **users**: Stores user information with role-based access
- **services**: Defines service types with unique prefixes
- **queues**: Manages queue numbers with status tracking
- **cache**: Application caching for performance

### Caching System
- **Services List Cache**: Caches service lists for 1 hour to improve performance
- **Queue Status Cache**: Caches queue status for 5 minutes for real-time updates
- **Current Queue Cache**: Caches currently called queues for 2 minutes
- **Next Queue Cache**: Caches next waiting queue for 2 minutes
- **Cache Invalidation**: Proper cache invalidation when services are created/updated/deleted
- **Performance Optimization**: Strategic caching to reduce database queries

### Models
#### User Model
- Implements soft deletes
- Role-based access control (admin, operator)
- Validation rules and helper methods
- Scopes for active, admin, and operator users

#### Service Model
- Implements soft deletes
- Relationship with queues
- Daily queue limit functionality
- Validation rules and helper methods

#### Queue Model
- Implements soft deletes
- Relationship with services
- Status transition validation
- Queue number generation with race condition prevention
- Estimated wait time calculation

### Controllers
#### Admin Controllers
- Dashboard with system statistics
- Service management (CRUD operations)
- User management (CRUD operations)
- Queue reset functionality

#### Operator Controllers
- Dashboard with service queues overview
- Queue management (call, done, cancel)
- Service-specific queue views
- Proper cache invalidation for real-time updates
- Enhanced security and validation
- Improved error handling and logging
- Performance optimizations with strategic caching

#### Patient Controllers
- Service selection interface
- Queue number generation
- Queue status tracking

### Views
- **Responsive Design**: Mobile-first approach with Tailwind CSS
- **Real-time Updates**: Live queue status display
- **User-Friendly Interface**: Intuitive navigation for all roles

## Installation

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (for asset compilation)

### Steps
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd siantre
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Copy environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure database in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=siantre
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Run migrations:
   ```bash
   php artisan migrate
   ```

8. Compile assets:
   ```bash
   npm run build
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

## Configuration

### Environment Variables
- `APP_NAME`: Application name
- `APP_ENV`: Environment (local, production)
- `APP_KEY`: Application encryption key
- `DB_*`: Database connection settings
- `MAIL_*`: Email configuration
- `LOG_*`: Logging configuration

### Custom Configuration
The system includes custom configurations for:
- Queue management settings
- Service prefixes
- Daily limits
- Caching strategies

## Usage Guide

### Admin Panel
1. Navigate to `/admin/dashboard`
2. Manage services and users
3. Monitor system statistics
4. Reset daily queues if needed

### Operator Panel
1. Navigate to `/operator/dashboard`
2. View service queues
3. Call, mark as done, or cancel queues
4. Monitor real-time queue status

### Patient Interface
1. Visit the homepage
2. Select a service
3. Generate queue number
4. Track queue status in real-time

### Anti-Duplication Rules (Patient Behavior)
- **One Queue Per Service Per Day**: Each IP address can only take one queue per service per day
- **Status-Based Validation**:
  - Cannot take new queue if previous queue is 'waiting' or 'called'
  - Can take new queue if previous queue is 'done' or 'canceled'
- **Cross-Service Flexibility**: Can take queues for different services simultaneously
- **Next-Day Availability**: Can take queue for same service again the next day
- **Error Messages**: Clear indication of existing queue status when attempting to take duplicate queue

## Business Logic

### Queue Status Transitions
- `waiting` → `called` → `done` (normal flow)
- `waiting` → `called` → `canceled` (if canceled after being called)
- `waiting` → `canceled` (if canceled before being called)

### Anti-Duplication Enforcement
- **Rule**: One active queue per IP per service per day
- **Exception**: Previous queue must be 'done' or 'canceled' to take new queue
- **Enforcement Point**: Queue generation endpoint (`/queue/generate`)
- **Validation**: Checks latest queue status for IP-service combination

## API Endpoints

### Authentication
- `GET /login` - Login page
- `POST /login` - Authenticate user
- `POST /logout` - Logout user

### Queue Management
- `GET /services` - List available services
- `POST /queue/generate` - Generate queue number
- `GET /queue/status/{number}` - Check queue status

### Admin Endpoints
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/services` - List services
- `POST /admin/services` - Create service
- `PUT /admin/services/{id}` - Update service
- `DELETE /admin/services/{id}` - Delete service
- `POST /admin/reset-queue` - Reset daily queues

### Operator Endpoints
- `GET /operator/dashboard` - Operator dashboard
- `GET /operator/queues/{service}` - Service queues
- `PUT /operator/queue/{queue}/call` - Call queue
- `PUT /operator/queue/{queue}/done` - Mark as done
- `PUT /operator/queue/{queue}/cancel` - Cancel queue

## Security Features

### Authentication & Authorization
- Role-based access control (RBAC)
- Two-Factor Authentication (2FA) for admin/operator accounts
- Secure password hashing (bcrypt)
- Session management
- CSRF protection

### Two-Factor Authentication (2FA)
- Implemented Google2FA for admin and operator accounts
- Users must configure 2FA during first login or when required
- QR code generation for easy setup with authenticator apps
- Backup codes available for emergency access
- Protected routes: All admin and operator routes require 2FA

### IP Whitelisting
- IP-based access control for admin and operator panels
- Configurable through database with UI management
- Support for multiple categories (admin, operator)
- Expiration dates for temporary access
- Real-time enforcement without code changes

### Granular Permissions System
- Fine-grained permission control using spatie/laravel-permission
- Role-based permissions (admin, operator, patient)
- Specific permissions for different actions:
  - Admin: view-dashboard, manage-services, manage-users, reset-queue, view-reports, manage-settings
  - Operator: view-operator-dashboard, view-queues, call-queue, complete-queue, cancel-queue
  - Patient: view-home, generate-queue, view-queue-status
- Dynamic permission assignment without code changes

### Session Management
- Role-based session timeouts:
  - Admin: 60 minutes
  - Operator: 120 minutes
  - Patient: 30 minutes
- Automatic session expiration
- Secure session handling

### Security Headers
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security with max-age configuration
- Referrer-Policy: no-referrer-when-downgrade
- Permissions-Policy for controlling browser features

### Data Protection
- Soft deletes for data preservation
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- Rate limiting to prevent abuse
- IP-based validation for queue generation

### Anti-Duplication Security
- IP address tracking for each queue
- Status-based validation to prevent queue abuse
- Business rule enforcement at application level
- Detailed error logging for suspicious activities

### IP Whitelist Management UI
- Admin interface for managing IP addresses
- Add/remove IP addresses with descriptions
- Categorize IPs (admin/operator access)
- Enable/disable individual IP addresses
- Set expiration dates for temporary access
- Bulk management capabilities

### Anti-Duplication Feature
- IP-based validation to prevent multiple queue generation
- Users can only take one queue per service per day unless previous queue is completed ('done') or canceled ('canceled')
- Detailed error messages showing existing queue number and status
- Database-level validation combined with application logic
- Business rule: Users must complete or cancel their current queue before taking a new one for the same service
- Enhanced security: Prevents users from gaming the system by taking multiple queues after being called
- Status-based validation: Checks all queue statuses (waiting, called, done, canceled) to enforce business rules

### Monitoring
- Comprehensive audit logging
- User activity tracking
- Error monitoring
- Performance metrics
- Security event logging

## Performance and Scalability Optimizations

### Caching Strategies
- **Redis-based caching**: Migrated from database to Redis for faster in-memory caching
- Service list caching (1 hour)
- Queue status caching (2 minutes)
- User permissions caching (30 minutes)
- Database query caching
- Strategic cache invalidation for real-time updates
- **Cache Tagging**: Implemented cache tags for more efficient cache invalidation (services, queues, operator)

### Database Optimizations
- **Comprehensive indexing strategy**: Added strategic indexes for frequently queried columns:
  - `idx_queues_status`: Index on status column for filtering by queue status
  - `idx_queues_service_status`: Composite index on service_id and status for common queries
  - `idx_queues_created_at`: Index on created_at for date-based queries
  - `idx_queues_service_created_at`: Composite index on service_id and created_at for date-based service queries
  - `idx_queues_service_status_created_at`: Composite index for complex queries involving service, status, and date
  - `idx_queues_queue_date`: Index on queue_date for date-based operations
  - `idx_queues_queue_date_status`: Composite index on queue_date and status
  - `idx_services_created_at`: Index on services table for ordering and filtering
- **Foreign Key Constraints**: Added foreign key constraints with cascade delete for data integrity
- **Read replicas support**: Configured read/write separation with support for database read replicas
- Query optimization
- Connection pooling
- Efficient relationship loading

### Background Processing
- **Queue-based background jobs**: Implemented background job processing for heavy operations
- Asynchronous processing to improve response times
- Job retry mechanisms and error handling
- Operations offloaded: Queue calling, completion, cancellation, and daily reset

### Asset Delivery
- **CDN integration support**: Configured support for serving static assets from CDN
- Optimized asset loading and delivery

### Code Optimizations
- Lazy loading for collections
- Efficient algorithms for queue generation
- Memory-efficient processing
- Asynchronous operations where appropriate
- Horizontal scaling capabilities through Redis and read replicas

### Queue Management Improvements
- **Enhanced Race Condition Prevention**: Improved locking mechanism in queue number generation to prevent duplicate numbers
- **Queue Expiration System**: Automatic detection and handling of expired queues (waiting > 4 hours, called > 2 hours)
- **Improved Status Validation**: Enhanced validation with detailed logging for invalid status transitions
- **Queue Cleanup Command**: Automated cleanup of old and expired queues with scheduling
- **Foreign Key Constraints**: Added database-level integrity with cascade delete for related records

### Error Handling Improvements
- **Comprehensive Error Logging**: Detailed error logging with context (user, IP, URL, trace)
- **User-Friendly Error Messages**: Clear, non-technical error messages for users
- **Context-Aware Logging**: Includes user agent, IP address, and full request context
- **Exception Class Tracking**: Logs specific exception types for better debugging

## Troubleshooting

### Common Issues

#### Queue Generation Issues
- **Problem**: Duplicate queue numbers
- **Solution**: Check unique constraint on `number` and `queue_date` columns

#### Authentication Issues
- **Problem**: Users unable to log in
- **Solution**: Verify password hashing and user roles

#### Performance Issues
- **Problem**: Slow page loads
- **Solution**: Clear cache and optimize database queries

#### Database Issues
- **Problem**: Migration errors
- **Solution**: Ensure proper database permissions and connectivity

### Debugging
Enable debug mode in `.env`:
```
APP_DEBUG=true
```

Check logs in `storage/logs/laravel.log` for detailed error information.

### Support
For additional support, contact the development team or refer to the official documentation.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes
4. Add tests
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Version History

### MVP 1.0
- Initial release with core queue management features
- Multi-role system (admin, operator, patient)
- Real-time queue display
- Service management
- User management
- Queue status tracking
- Daily queue reset
- Comprehensive logging