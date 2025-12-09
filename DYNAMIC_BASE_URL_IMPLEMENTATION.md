# Dynamic Base URL Implementation

## What Was Changed

This implementation ensures the system works correctly regardless of where it's deployed - whether at the root domain or in a subdirectory.

## Problem Solved

**Before:**
- JavaScript fetch calls used hardcoded URLs like `/users/123/profile`
- Would fail if deployed to `https://company.com/subay/` (subdirectory)
- The `/users/123/profile` would try to hit `https://company.com/users/123/profile` instead of `https://company.com/subay/users/123/profile`

**After:**
- All fetch calls now use `url('users/123/profile')` 
- Automatically prepends the correct base URL from config
- Works in any deployment scenario

## Changes Made

### 1. Layout Files Updated
Added BASE_URL configuration to all main layouts:
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/superadmin.blade.php`
- `resources/views/layouts/RegionalAdmin.blade.php`
- `resources/views/layouts/ProvincialAdmin.blade.php`

**Added Script:**
```html
<script>
    window.BASE_URL = "{{ rtrim(config('app.url'), '/') }}";
</script>
```

### 2. Bootstrap.js Helper Function
Added a global `url()` helper function in `resources/js/bootstrap.js`:

```javascript
window.url = function(path) {
    path = path.replace(/^\//, '');
    return `${window.BASE_URL}/${path}`;
};
```

### 3. Updated Fetch Calls

**Files Modified:**
- `resources/views/superadmin/users_nav/users.blade.php` (5 fetch calls)
- `resources/views/superadmin/archives_nav/archives.blade.php` (3 fetch calls)
- `resources/views/superadmin/officials/places_management.blade.php` (5 fetch calls)
- `resources/views/superadmin/officials/index.blade.php` (3 fetch calls)

**Example Changes:**
```javascript
// BEFORE
fetch('/users/123/profile')
fetch('/api/places/provinces')
fetch('/import/progress')

// AFTER
fetch(url('users/123/profile'))
fetch(url('api/places/provinces'))
fetch(url('import/progress'))
```

## Configuration

The base URL is controlled by the `APP_URL` in your `.env` file:

```env
# Development (root)
APP_URL=http://localhost

# Production (subdirectory)
APP_URL=https://company.com/subay

# Production (root)
APP_URL=https://subay.company.com
```

## Testing

1. **Local Development:** Should work as before
2. **Subdirectory Deployment:** Update `.env` with the full URL including subdirectory
3. **No code changes needed** when moving between environments

## Notes

- Forms using Laravel routes (`form.action`) already work correctly
- Pagination links are already absolute URLs from Laravel
- Only manual fetch calls needed updating

## Deployment Checklist

When deploying to production:
1. ✅ Update `APP_URL` in `.env` to match server configuration
2. ✅ Run `php artisan config:cache`
3. ✅ Run `npm run build` to compile JavaScript with new changes
4. ✅ Test all AJAX functionality (users, archives, places, officials)

---
**Implementation Date:** December 9, 2025
**Status:** ✅ Complete and Ready for Production
