# 🚀 NORMALIZED DATABASE IMPLEMENTATION GUIDE

## Step-by-Step Instructions for Creating a New Normalized Database

---

## ⚠️ IMPORTANT WARNINGS

1. **BACKUP YOUR DATABASE FIRST!** This cannot be undone.
2. **Test on a development database** before production.
3. **This will create a NEW database structure** - your current data needs to be migrated.
4. **Estimated time**: 30-60 minutes depending on data size.

---

## 📋 PREREQUISITES

- [x] PHP 8.1 or higher
- [x] Laravel 10.x
- [x] MySQL 8.0 or higher
- [x] Database backup completed
- [x] Development environment (not production)

---

## 🎯 IMPLEMENTATION OPTIONS

### Option A: Fresh Database (Recommended for New Projects)

Use this if:
- Starting from scratch
- No important data to preserve
- Want the cleanest structure

### Option B: Migrate Existing Data (Recommended for Your Case)

Use this if:
- Have existing data
- Need to preserve user accounts, inventory, FETS documents
- Want to transition smoothly

---

## 📝 OPTION A: FRESH DATABASE SETUP

### Step 1: Create New Database

```powershell
# In PowerShell terminal
mysql -u root -p
```

```sql
-- In MySQL prompt
CREATE DATABASE subay_pantawid_normalized CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE subay_pantawid_normalized;
EXIT;
```

### Step 2: Update .env File

```env
DB_DATABASE=subay_pantawid_normalized
```

### Step 3: Run Migration

```powershell
php artisan migrate:fresh
```

This will create all the normalized tables with optimized column sizes!

### Step 4: Seed Initial Data (Optional)

```powershell
php artisan db:seed
```

---

## 📦 OPTION B: MIGRATE EXISTING DATA (RECOMMENDED)

### Phase 1: Preparation (5 minutes)

#### Step 1.1: Backup Current Database

```powershell
# Create backup directory
New-Item -ItemType Directory -Force -Path "c:\laragon\www\subay_pantawid\backups"

# Backup database
mysqldump -u root -p subay_pantawid > "c:\laragon\www\subay_pantawid\backups\backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
```

#### Step 1.2: Verify Backup

```powershell
# Check if backup file exists and has content
Get-Item "c:\laragon\www\subay_pantawid\backups\backup_*.sql" | Select-Object Name, Length
```

#### Step 1.3: Create Test Database

```powershell
mysql -u root -p
```

```sql
CREATE DATABASE subay_pantawid_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

### Phase 2: Rename Existing Tables (10 minutes)

We'll rename existing tables to keep them as backup while creating new normalized structure.

#### Step 2.1: Connect to Database

```powershell
mysql -u root -p subay_pantawid
```

#### Step 2.2: Rename Tables

```sql
-- Core tables
RENAME TABLE users TO users_old;
RENAME TABLE inventory TO inventory_old;
RENAME TABLE fets_documents TO fets_documents_old;
RENAME TABLE fets_logs TO fets_logs_old;
RENAME TABLE officials TO officials_old;
RENAME TABLE officials_history TO officials_history_old;
RENAME TABLE repair_destinations TO repair_destinations_old;

-- Optional tables (if they exist)
RENAME TABLE places TO places_old;
RENAME TABLE import_progress TO import_progress_old;
RENAME TABLE manuals TO manuals_old;

-- Laravel tables (keep as is - no need to rename)
-- password_reset_tokens, personal_access_tokens, failed_jobs, etc.

EXIT;
```

---

### Phase 3: Create Normalized Structure (5 minutes)

#### Step 3.1: Run Normalized Database Migration

```powershell
# This creates all the new normalized tables
php artisan migrate --path=database/migrations/2025_11_10_000000_create_normalized_database.php
```

You should see:
```
✓ Migration: 2025_11_10_000000_create_normalized_database
   - Created: places
   - Created: users
   - Created: inventory
   - Created: officials
   - Created: officials_history
   - Created: repair_destinations
   - Created: fets_documents
   - Created: fets_items (NEW!)
   - Created: fets_logs
   - Created: import_progress
   - Created: manuals
   - Created: activity_log
```

---

### Phase 4: Migrate Data (15-30 minutes)

#### Step 4.1: Run Data Migration

```powershell
php artisan migrate --path=database/migrations/2025_11_10_000001_migrate_data_to_normalized_structure.php
```

You should see progress like:
```
✓ Places data migrated
✓ Users data migrated (250 users)
✓ Inventory data migrated (5,420 items)
✓ Officials data migrated (15 officials)
✓ Repair destinations data migrated
✓ FETS documents data migrated (380 documents)
✓ FETS logs data migrated (1,240 logs)
...
✅ Data migration completed successfully!
```

#### Step 4.2: Verify Data Migration

```powershell
# Check record counts
php artisan tinker
```

```php
// In Tinker
\App\Models\User::count();
\App\Models\Inventory::count();
\App\Models\FetsDocument::count();
DB::table('fets_items')->count(); // Should be > FETS documents count

exit
```

---

### Phase 5: Update Application Code (20-40 minutes)

#### Step 5.1: Update Models

Create/Update: `app/Models/FetsItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FetsItem extends Model
{
    protected $fillable = [
        'fets_document_id',
        'property_no',
        'item_status',
        'item_remarks',
    ];

    // Relationship to FETS Document
    public function fetsDocument()
    {
        return $this->belongsTo(FetsDocument::class);
    }

    // Relationship to Inventory
    public function inventoryItem()
    {
        return $this->belongsTo(Inventory::class, 'property_no', 'property_no');
    }
}
```

Update: `app/Models/FetsDocument.php`

```php
// Add this to your existing FetsDocument model

public function items()
{
    return $this->hasMany(FetsItem::class);
}

// Update property access
public function getPropertyNumbersAttribute()
{
    return $this->items->pluck('property_no')->toArray();
}
```

Update: `app/Models/User.php`

```php
// Add this to your existing User model

public function office()
{
    return $this->belongsTo(Place::class, 'office_id');
}

// Get province through office relationship
public function getProvinceAttribute()
{
    if ($this->office && $this->office->parent && $this->office->parent->parent) {
        return $this->office->parent->parent->name;
    }
    return null;
}

// Get municipality through office relationship
public function getMunicipalityAttribute()
{
    if ($this->office && $this->office->parent) {
        return $this->office->parent->name;
    }
    return null;
}
```

Update: `app/Models/Inventory.php`

```php
// Add this to your existing Inventory model

public function receiver()
{
    return $this->belongsTo(User::class, 'receiver_user_id');
}

// Accessor for backward compatibility
public function getReceiverNameAttribute()
{
    return $this->receiver ? $this->receiver->fullname : null;
}
```

Create: `app/Models/Place.php` (if not exists)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'parent_id', 'name', 'code', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Parent place (municipality for office, province for municipality)
    public function parent()
    {
        return $this->belongsTo(Place::class, 'parent_id');
    }

    // Child places
    public function children()
    {
        return $this->hasMany(Place::class, 'parent_id');
    }

    // Users in this place
    public function users()
    {
        return $this->hasMany(User::class, 'office_id');
    }

    // Scope: Get provinces
    public function scopeProvinces($query)
    {
        return $query->where('type', 'province');
    }

    // Scope: Get municipalities
    public function scopeMunicipalities($query)
    {
        return $query->where('type', 'municipality');
    }

    // Scope: Get offices
    public function scopeOffices($query)
    {
        return $query->where('type', 'office');
    }
}
```

#### Step 5.2: Update Controllers

**Example: FetsDocumentController**

```php
// OLD: Store comma-separated property numbers
public function store(Request $request)
{
    $fets = FetsDocument::create([
        'property_no' => implode(',', $request->property_numbers), // ❌ OLD WAY
        // ... other fields
    ]);
}

// NEW: Store individual items in fets_items table
public function store(Request $request)
{
    $fets = FetsDocument::create([
        // property_no column no longer exists
        // ... other fields
    ]);

    // Create FETS items (1NF compliant!)
    foreach ($request->property_numbers as $propertyNo) {
        $fets->items()->create([
            'property_no' => $propertyNo,
            'item_status' => 'pending',
        ]);
    }
}
```

**Example: UserController**

```php
// OLD: Store location as text
public function store(Request $request)
{
    User::create([
        'province' => $request->province,      // ❌ OLD WAY
        'municipality' => $request->municipality,
        'office' => $request->office,
        // ... other fields
    ]);
}

// NEW: Store office_id reference
public function store(Request $request)
{
    User::create([
        'office_id' => $request->office_id,    // ✅ NEW WAY (normalized)
        // ... other fields
    ]);
}
```

#### Step 5.3: Update Views

**Example: FETS Document Form**

```blade
{{-- OLD: Display comma-separated property numbers --}}
<p>Properties: {{ $fetsDocument->property_no }}</p>

{{-- NEW: Display from fets_items relationship --}}
<ul>
    @foreach($fetsDocument->items as $item)
        <li>{{ $item->property_no }} - {{ $item->inventoryItem->article_description }}</li>
    @endforeach
</ul>
```

**Example: User Form**

```blade
{{-- OLD: Three separate dropdowns --}}
<select name="province">...</select>
<select name="municipality">...</select>
<select name="office">...</select>

{{-- NEW: Single cascading dropdown --}}
<select name="office_id" id="office_id">
    @foreach($offices as $office)
        <option value="{{ $office->id }}">
            {{ $office->parent->parent->name }} > {{ $office->parent->name }} > {{ $office->name }}
        </option>
    @endforeach
</select>
```

---

### Phase 6: Testing (15-30 minutes)

#### Step 6.1: Test User Authentication

```powershell
# Try logging in with existing users
php artisan serve
# Visit http://localhost:8000 and login
```

#### Step 6.2: Test FETS Creation

1. Create a new FETS document
2. Verify property items are stored in `fets_items` table
3. Check that you can view all properties in the FETS

#### Step 6.3: Test Inventory

1. View inventory list
2. Check that receiver names display correctly (from `users` table)
3. Verify search and filters work

#### Step 6.4: Test Reports

1. Generate reports
2. Verify all data displays correctly
3. Check PDF generation

#### Step 6.5: Run Automated Tests

```powershell
php artisan test
```

---

### Phase 7: Cleanup (5 minutes)

#### Step 7.1: Verify Everything Works

- [ ] Users can log in
- [ ] FETS documents display correctly
- [ ] Inventory shows proper receiver names
- [ ] Reports generate successfully
- [ ] All functionality works as before

#### Step 7.2: Drop Old Tables (ONLY AFTER VERIFICATION!)

```powershell
mysql -u root -p subay_pantawid
```

```sql
-- ⚠️ ONLY DO THIS AFTER CONFIRMING EVERYTHING WORKS!
-- This permanently deletes the old tables

DROP TABLE IF EXISTS users_old;
DROP TABLE IF EXISTS inventory_old;
DROP TABLE IF EXISTS fets_documents_old;
DROP TABLE IF EXISTS fets_logs_old;
DROP TABLE IF EXISTS officials_old;
DROP TABLE IF EXISTS officials_history_old;
DROP TABLE IF EXISTS repair_destinations_old;
DROP TABLE IF EXISTS places_old;
DROP TABLE IF EXISTS import_progress_old;
DROP TABLE IF EXISTS manuals_old;

EXIT;
```

---

## 🎉 COMPLETION CHECKLIST

- [ ] Database backup created
- [ ] Old tables renamed
- [ ] New normalized tables created
- [ ] Data migrated successfully
- [ ] Models updated
- [ ] Controllers updated
- [ ] Views updated
- [ ] All tests passing
- [ ] User authentication works
- [ ] FETS creation works
- [ ] Inventory display works
- [ ] Reports generate correctly
- [ ] Old tables dropped (after verification)

---

## 🔍 VERIFICATION QUERIES

Run these queries to verify your normalized database:

```sql
-- 1. Check if fets_items table has data (should be > 0)
SELECT COUNT(*) FROM fets_items;

-- 2. Check if users have office_id (should match user count)
SELECT COUNT(*) FROM users WHERE office_id IS NOT NULL;

-- 3. Check if inventory has receiver_user_id
SELECT COUNT(*) FROM inventory WHERE receiver_user_id IS NOT NULL;

-- 4. Verify no comma-separated values in fets_items
SELECT * FROM fets_items WHERE property_no LIKE '%,%';
-- Should return 0 rows!

-- 5. Check places hierarchy
SELECT 
    p.name as province,
    m.name as municipality,
    o.name as office
FROM places o
INNER JOIN places m ON o.parent_id = m.id
INNER JOIN places p ON m.parent_id = p.id
WHERE o.type = 'office'
LIMIT 10;
```

---

## 🆘 TROUBLESHOOTING

### Issue: Data Migration Fails

**Solution:**
```powershell
# Rollback and try again
php artisan migrate:rollback --step=1

# Check for data issues
mysql -u root -p subay_pantawid
SELECT * FROM users_old WHERE province IS NULL OR office IS NULL;
EXIT;
```

### Issue: Foreign Key Constraint Errors

**Solution:**
```powershell
# Disable foreign key checks temporarily
mysql -u root -p subay_pantawid
```
```sql
SET FOREIGN_KEY_CHECKS=0;
-- Run your migration
SET FOREIGN_KEY_CHECKS=1;
```

### Issue: Old Tables Still Exist

**Solution:** The old tables are kept as backup. Only drop them after confirming everything works perfectly!

---

## 📚 ADDITIONAL RESOURCES

- [DATABASE_NORMALIZATION_GUIDE.md](./DATABASE_NORMALIZATION_GUIDE.md) - Theory and concepts
- [COLUMN_SIZE_OPTIMIZATION_GUIDE.md](./COLUMN_SIZE_OPTIMIZATION_GUIDE.md) - Size optimization details
- [Laravel Documentation](https://laravel.com/docs/10.x/migrations) - Migration reference

---

## 🎓 WHAT YOU'VE ACHIEVED

✅ **1NF Compliance**: No more comma-separated values in `fets_documents.property_no`
✅ **3NF Compliance**: No transitive dependencies (office_id instead of province/municipality/office text)
✅ **Optimized Columns**: Right-sized columns for performance
✅ **Better Relationships**: Proper foreign keys and referential integrity
✅ **Easier Queries**: Simple joins instead of string parsing
✅ **Data Consistency**: Changes in one place reflect everywhere

---

Good luck with your database normalization! 🚀✨

---

**Need Help?** Review the guides:
- Theory: [DATABASE_NORMALIZATION_GUIDE.md](./DATABASE_NORMALIZATION_GUIDE.md)
- Column Sizes: [COLUMN_SIZE_OPTIMIZATION_GUIDE.md](./COLUMN_SIZE_OPTIMIZATION_GUIDE.md)
