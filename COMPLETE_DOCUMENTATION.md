# SiAntre (Sistem Antrian Online) - Complete System Documentation

## Table of Contents
1. [Overview](#overview)
2. [Core Features](#core-features)
3. [Security Features](#security-features)
4. [Advanced Features](#advanced-features)
5. [Installation & Deployment](#installation--deployment)
6. [User Manuals](#user-manuals)
7. [Maintenance & Troubleshooting](#maintenance--troubleshooting)

## Overview

SiAntre (Sistem Antrian Online) is a comprehensive online queue management system designed for clinics, workshops, salons, and village offices. The system provides a web-based solution for managing queues with real-time updates and role-based access control.

## Core Features

### Multi-role System
- **Admin**: Full system management capabilities
- **Operator**: Queue management and service operations
- **Patient/Visitor**: Queue generation and status tracking

### Queue Management
- Real-time queue generation with unique numbering
- Status tracking (waiting, called, done, canceled)
- Estimated wait time calculation
- Position in queue tracking

### Service Management
- Multiple service types with unique prefixes
- Daily queue limits
- Service-specific queue management

### Real-time Display
- Public display screen with live queue information
- Automatic refresh for real-time updates
- Clear visual indicators for queue status

## Security Features

### Authentication & Authorization
- **Role-based Access Control (RBAC)**: Different permissions for each role
- **Two-Factor Authentication (2FA)**: Additional security layer for admin/operator accounts
- **Secure Password Hashing**: Using bcrypt algorithm
- **Session Management**: Proper session handling with role-based timeouts

### Two-Factor Authentication (2FA)
- Implemented using Google2FA package
- Required for admin and operator accounts
- QR code generation for easy setup
- Protected routes: All admin and operator routes require 2FA

### IP Whitelisting
- **Database-driven approach**: IPs stored in database with UI management
- **Categories**: Admin and operator access categories
- **Expiration dates**: Temporary access with automatic expiration
- **Management interface**: Add, edit, delete, and toggle IPs through admin panel

### Granular Permissions System
- **Fine-grained control**: Specific permissions for different actions
- **Dynamic assignment**: Permissions can be changed without code changes
- **Role-based**: Permissions assigned to roles, roles assigned to users

### Security Headers
- **X-Content-Type-Options**: Prevents MIME type sniffing
- **X-Frame-Options**: Prevents clickjacking
- **X-XSS-Protection**: Enables browser XSS protection
- **Strict-Transport-Security**: Enforces HTTPS
- **Referrer-Policy**: Controls referrer information
- **Permissions-Policy**: Controls browser features

### Session Management
- **Role-based timeouts**:
  - Admin: 60 minutes
  - Operator: 120 minutes
  - Patient: 30 minutes
- **Automatic expiration**: Sessions expire based on timeout settings
- **Secure handling**: Proper session security measures

### Data Protection
- **Soft Deletes**: Data preservation with soft delete functionality
- **Input Validation**: Comprehensive validation and sanitization
- **SQL Injection Prevention**: Parameterized queries and proper escaping
- **XSS Protection**: Output encoding and proper escaping

## Advanced Features

### Caching System
- **Service List Cache**: Caches service lists for 1 hour
- **Queue Status Cache**: Caches queue status for 5 minutes
- **Performance Optimization**: Strategic caching to reduce database queries
- **Cache Invalidation**: Proper invalidation for real-time updates

### Monitoring & Logging
- **Comprehensive Audit Trail**: Full logging of user activities
- **Security Event Logging**: Tracking of security-related events
- **Performance Metrics**: Monitoring of system performance
- **Error Monitoring**: Detailed error logging for troubleshooting

### Automated Operations
- **Daily Queue Reset**: Automatic reset of queues at midnight
- **Cron Job Integration**: Scheduled tasks for routine operations
- **Queue Estimation Algorithm**: Intelligent wait time calculation

## Installation & Deployment

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (for asset compilation)

### Installation Steps
1. Clone the repository
2. Install PHP dependencies with Composer
3. Install Node.js dependencies and build assets
4. Configure environment variables
5. Run database migrations
6. Start the development server

### Deployment Configuration
- Environment variables for security settings
- Required migrations for security features
- Cron job setup for automated operations
- Web server configuration for production

### Security Configuration
- Enable 2FA for admin/operator accounts
- Configure IP whitelist for admin access
- Set up role-based permissions
- Configure session timeouts appropriately

## User Manuals

### Administrator Manual
- System dashboard and statistics
- Service management (CRUD operations)
- User management (CRUD operations)
- Queue reset functionality
- IP whitelist management

### Operator Manual
- Queue management interface
- Calling and completing queues
- Service-specific queue views
- Real-time queue status monitoring

### Patient Manual
- Service selection interface
- Queue number generation
- Queue status tracking
- Estimated wait times

## Maintenance & Troubleshooting

### Regular Maintenance
- Review IP whitelist periodically
- Audit user permissions regularly
- Monitor security logs
- Update security patches

### Troubleshooting Common Issues
- Queue generation problems
- Authentication issues
- Performance issues
- Security-related problems

### Monitoring
- System performance metrics
- Security event logs
- User activity tracking
- Error reporting and resolution

## Version Information

### MVP 1.0 Features
- Complete queue management system
- Multi-role access control
- Real-time display functionality
- Security enhancements (2FA, IP whitelisting, permissions)
- Automated queue reset
- Comprehensive logging and monitoring

## Support & Contact

For additional support:
- Check system logs in `storage/logs/laravel.log`
- Contact the development team
- Refer to specific manual documents for role-based guidance
- Backup system before major changes

---

This documentation provides a comprehensive overview of the SiAntre system, covering all core functionality and advanced security features implemented to ensure a secure and efficient queue management solution.