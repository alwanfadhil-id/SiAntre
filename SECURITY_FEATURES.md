# Security Features Documentation - SiAntre (Sistem Antrian Online)

## Overview

This document describes the security features implemented in the SiAntre application to protect against various threats and ensure secure access to the system.

## Two-Factor Authentication (2FA)

### Implementation
- Uses Google2FA package for Laravel
- Implemented for admin and operator accounts
- Requires 2FA verification after successful login
- Protected routes: All admin and operator routes require 2FA

### Configuration
- Environment variables:
  - `OTP_ENABLED=true` - Enable/disable 2FA
  - `OTP_LIFETIME=0` - Lifetime of OTP in minutes (0 = eternal)
  - `OTP_KEEP_ALIVE=true` - Renew lifetime at every request
  - `OTP_THROW_EXCEPTION=false` - Whether to throw exceptions or fire events

### User Experience
1. User logs in with username/password
2. System redirects to 2FA verification page
3. User enters 6-digit code from authenticator app
4. System verifies code and grants access if valid

## IP Whitelisting

### Database-Driven Approach
- IP addresses stored in `ip_whitelists` table
- Supports multiple categories: admin, operator
- Includes expiration dates for temporary access
- Allows enabling/disabling without deletion

### Categories
- **Admin**: Access to admin panel and sensitive operations
- **Operator**: Access to operator dashboard and queue management

### Management Interface
- Available at `/admin/ip-whitelist`
- Allows adding, editing, deleting IP addresses
- Supports toggling active status
- Supports setting expiration dates

## Granular Permissions System

### Implementation
- Uses spatie/laravel-permission package
- Role-based permissions with fine-grained control
- Dynamic permission assignment without code changes

### Roles and Permissions

#### Admin Permissions
- `view-dashboard` - Access to admin dashboard
- `manage-services` - Create, edit, delete services
- `manage-users` - Create, edit, delete users
- `reset-queue` - Reset daily queues
- `view-reports` - Access to system reports
- `manage-settings` - Access to system settings

#### Operator Permissions
- `view-operator-dashboard` - Access to operator dashboard
- `view-queues` - View service queues
- `call-queue` - Call next queue
- `complete-queue` - Mark queue as done
- `cancel-queue` - Cancel queue

#### Patient Permissions
- `view-home` - Access to home page
- `generate-queue` - Generate queue number
- `view-queue-status` - View queue status

## Session Management

### Role-Based Timeouts
- **Admin**: 60 minutes
- **Operator**: 120 minutes
- **Patient**: 30 minutes

### Implementation
- Uses `RoleBasedSessionTimeout` middleware
- Dynamically adjusts session lifetime based on user role
- Automatically applies appropriate timeout

## Security Headers

### Implemented Headers
- `X-Content-Type-Options: nosniff` - Prevents MIME type sniffing
- `X-Frame-Options: DENY` - Prevents clickjacking
- `X-XSS-Protection: 1; mode=block` - Enables XSS protection
- `Strict-Transport-Security` - Enforces HTTPS
- `Referrer-Policy: no-referrer-when-downgrade` - Controls referrer information
- `Permissions-Policy` - Controls browser features

### Implementation
- Uses `SecurityHeaders` middleware
- Applied globally to all responses

## Security Best Practices

### Input Validation
- All user inputs are validated
- Sanitized before processing
- Prevents injection attacks

### Error Handling
- Detailed error logging for administrators
- Generic error messages for users
- Prevents information disclosure

### Audit Trail
- Comprehensive logging of user activities
- Security event logging
- Access control violation tracking

## Configuration Requirements

### Environment Variables
```
# Two-Factor Authentication
OTP_ENABLED=true
OTP_LIFETIME=0
OTP_KEEP_ALIVE=true
OTP_THROW_EXCEPTION=false

# IP Whitelisting
ADMIN_ALLOWED_IPS=127.0.0.1,::1
```

### Required Migrations
- `add_google2fa_secret_to_users_table`
- `create_permission_tables`
- `create_ip_whitelists_table`

## Deployment Considerations

### Production Setup
1. Ensure environment variables are properly configured
2. Run all required migrations
3. Seed initial roles and permissions
4. Configure IP whitelist for admin access
5. Test 2FA functionality

### Security Monitoring
- Monitor authentication logs
- Track permission changes
- Watch for IP access violations
- Regular security audits

## Troubleshooting

### Common Issues

#### 2FA Not Working
- Verify environment variables are set correctly
- Check that Google2FA package is properly installed
- Ensure user has 2FA enabled and configured

#### IP Whitelist Blocking Access
- Verify IP addresses are correctly entered in database
- Check that IPs are marked as active
- Ensure expiration dates are in the future

#### Permission Issues
- Verify user has correct role assigned
- Check that role has required permissions
- Clear permission cache if changes were made programmatically

## Maintenance

### Regular Tasks
- Review IP whitelist periodically
- Audit user permissions regularly
- Monitor security logs
- Update security patches

### Updates
- Keep security packages updated
- Review and update permissions as needed
- Test security features after updates
- Update documentation as needed