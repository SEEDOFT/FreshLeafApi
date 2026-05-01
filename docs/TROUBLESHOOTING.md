# Troubleshooting Guide

## Common Issues and Solutions

## API Issues

### 401 Unauthorized
- **Cause**: Invalid or expired token
- **Solution**: Re-authenticate and get new token

### 403 Forbidden
- **Cause**: User type not authorized for endpoint
- **Solution**: Check user type matches required type (user/admin/vendor)

### 422 Validation Error
- **Cause**: Invalid request data
- **Solution**: Check validation rules in controller

---

## Authentication Issues

### PIN Verification Failed
- **Cause**: Incorrect PIN entered
- **Solution**: Verify user has `set_pin = true` in profile

### Password Reset Failed
- **Cause**: Invalid current password
- **Solution**: Verify current password before updating

---

## Real-time Issues

### WebSocket Connection Failed
- **Cause**: Reverb not running
- **Solution**: Run `php artisan reverb:start`

### Echo Not Available in Admin
- **Cause**: JS assets not built
- **Solution**: Run `npm run build` in FreshLeafApi

### Channel Authorization Failed (403)
- **Cause**: User not authorized for channel
- **Solution**: Check `routes/channels.php` authorization rules

### Events Not Received
- **Cause**: Event name mismatch
- **Solution**: Verify event broadcast name matches listener

---

## Database Issues

### Migration Failed
- **Cause**: Database connection issues
- **Solution**: Check `.env` database settings

### Foreign Key Error
- **Cause**: Related record doesn't exist
- **Solution**: Ensure parent records exist first

---

## Payment Issues

### Wallet Balance Not Updating
- **Cause**: Transaction not marked complete
- **Solution**: Check transaction status

### Payment Method Validation Failed
- **Cause**: Invalid card details
- **Solution**: Implement proper validation (Luhn algorithm, etc.)

---

## Admin Panel Issues

### Filament Login Not Working
- **Cause**: Session auth issue
- **Solution**: Clear browser cache and try again

### Widgets Not Loading
- **Cause**: Query performance issues
- **Solution**: Add proper eager loading

---

## Flutter App Issues

### API Connection Failed
- **Cause**: Wrong API URL
- **Solution**: Check `.env.local` API_URL

### Token Not Saved
- **Cause**: Storage service issue
- **Solution**: Verify StorageService implementation

### WebSocket Disconnects After 10 Seconds
- **Cause**: No reconnection mechanism
- **Solution**: Implement reconnection in SupportRealtimeService

---

## Performance Issues

### Slow API Response
- **Cause**: N+1 query problem
- **Solution**: Use eager loading (`with()`)

### Memory Issues
- **Cause**: Large dataset processing
- **Solution**: Use pagination and chunking

---

## Getting Help

1. Check Laravel log: `storage/logs/laravel.log`
2. Check browser console for JS errors
3. Verify environment variables in `.env`
4. Run tests: `php artisan test`
5. Clear caches: `php artisan optimize:clear`