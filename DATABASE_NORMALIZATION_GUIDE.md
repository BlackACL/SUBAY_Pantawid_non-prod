# 📚 SUBAY PANTAWID DATABASE NORMALIZATION GUIDE
## For Beginners - Step by Step Understanding

---

## 🎯 PART 1: UNDERSTANDING YOUR DATABASE

### What is a Database?
Think of a database like a **filing cabinet** with multiple drawers (tables). Each drawer holds specific types of information organized in folders (rows) with labels (columns).

### What are Entities and Attributes?

**ENTITY** = A "thing" or "object" you want to store information about (like a table)
- Example: `users`, `inventory`, `fets_documents`

**ATTRIBUTE** = A piece of information about that entity (like a column)
- Example: In `users` table → `fullname`, `email`, `province` are attributes

---

## 📊 YOUR DATABASE TABLES EXPLAINED

### 1. **USERS TABLE** (Who uses the system)
**Purpose**: Stores all people who can log into the system

**Attributes Explained:**
- `id` - Unique number for each person
- `fullname` - Person's complete name
- `email` - Email address for login
- `password` - Encrypted password
- `province` - Where they work (Davao De Oro, Davao Del Norte, etc.)
- `municipality` - City/municipality they work in
- `office` - Specific office location
- `access_level` - Their role (Employee, Provincial DPSC, Regional DPSC, Superadmin)
- `contact_number` - Phone number
- `active` - Is account enabled? (Yes/No)
- `two_factor_enabled` - Using 2FA? (Yes/No)
- `archived_at` - When was account deactivated
- `failed_attempts` - How many wrong password attempts
- `locked_until` - Account locked until this time

**What it's for**: Authentication, authorization, and user management

---

### 2. **INVENTORY TABLE** (Equipment/Assets)
**Purpose**: All government equipment and their details

**Attributes Explained:**
- `FUND_CODE` - Where money came from
- `PROPERTY_STATUS` - Renewed/Issued/Returned
- `ARTICLE_DESCRIPTION` - Type of item (Chair, Computer, etc.)
- `GENERAL_DESCRIPTION` - Detailed description
- `SERIAL_NO` - Manufacturer's serial number
- `PROPERTY_NO` - Government property number (like a barcode)
- `PAR_NO` - Property Acknowledgment Receipt number
- `PAR_DATE` - When PAR was issued
- `UNIT` - Unit of measurement
- `QTY` - Quantity
- `ACQUISITION_COST` - How much it cost
- `ACQUISITION_DATE` - When it was purchased
- `RECEIVER` - Who is currently using it
- `ISSUED_TO` - Who it was issued to
- `DPO_REMARKS` - Property Officer's notes
- `STATUS` - Current status (Operational, For Repair, etc.)
- `source_file` - Which Excel file it came from

**What it's for**: Tracking all government property

---

### 3. **FETS_DOCUMENTS TABLE** (Transfer Forms)
**Purpose**: Stores Furniture, Equipment Transfer Slip documents

**Attributes Explained:**
- `user_id` - Who created this FETS
- `property_no` - Which properties are being transferred (comma-separated)
- `to_receiver` - Who will receive the items
- `status` - submitted/verified/approved/rejected
- `remarks` - Serviceable/Unserviceable
- `transfer_movement` - Type: Issue/Transfer, For Repair, For Surrender, Return to Lender
- `repair_destination` - Where to repair (if For Repair)
- `file_name` - PDF filename
- `file_path` - Where PDF is stored
- `verified_by` - Who verified it (official ID)
- `rejected_by` - Who rejected it (if rejected)
- `approved_by` - Who approved it (official ID)
- `rejected_remarks` - Why was it rejected

**What it's for**: Document workflow for transferring equipment

---

### 4. **FETS_LOGS TABLE** (History of FETS)
**Purpose**: Track every action on FETS documents

**Attributes Explained:**
- `property_no` - Equipment involved
- `action` - What happened (verified/approved/rejected)
- `actor` - Who did it
- `actor_role` - Their position
- `remarks` - Additional notes

**What it's for**: Audit trail and history tracking

---

### 5. **OFFICIALS TABLE** (Signing Officials)
**Purpose**: People who sign documents (DPSC, Head of Property, etc.)

**Attributes Explained:**
- `province` - Which province (if Provincial DPSC)
- `role` - Provincial DPSC, Regional DPSC, Head of Property, Recommending, Approving
- `fullname` - Official's name
- `user_id` - Link to users table (if they have account)
- `active` - Currently in this position?

**What it's for**: Know who signs FETS documents

---

### 6. **OFFICIALS_HISTORY TABLE**
**Purpose**: Track changes to officials over time

**What it's for**: Historical record of who held what position when

---

### 7. **PLACES TABLE** (Locations)
**Purpose**: Organizational structure of locations

**Attributes Explained:**
- `type` - province/municipality/office
- `parent_id` - Which location it belongs to
- `name` - Location name

**What it's for**: Hierarchical location structure

---

### 8. **REPAIR_DESTINATIONS TABLE**
**Purpose**: Where equipment can be sent for repair

**Attributes Explained:**
- `name` - Repair shop/facility name

**What it's for**: List of approved repair locations

---

### 9. **ACTIVITY_LOG TABLE**
**Purpose**: System activity tracking (using Spatie package)

**What it's for**: Track all user actions in the system

---

### 10. **IMPORT_PROGRESS TABLE**
**Purpose**: Track CSV file imports

**What it's for**: Monitor bulk data uploads

---

### 11. **MANUALS TABLE**
**Purpose**: Store user manual PDF files

**Attributes Explained:**
- `filename` - Original file name
- `original_name` - Display name
- `file_size` - Size in bytes

**What it's for**: Help documentation storage

---

### 12. **PERMISSIONS, ROLES, MODEL_HAS_ROLES, etc.**
**Purpose**: Laravel permission system (Spatie)

**What it's for**: Control who can do what in the system

---

## 🔍 PART 2: NORMALIZATION EXPLAINED SIMPLY

### What is Normalization?
**Normalization** = Organizing your database to:
1. **Avoid duplicate data** (don't repeat the same information)
2. **Make updates easier** (change in one place, not everywhere)
3. **Prevent data problems** (inconsistencies)

---

## 📝 THE 3 NORMAL FORMS (Simple Explanation)

### **1NF (First Normal Form)** - "No Repeating Groups"

**Rule**: Each cell must have only ONE value (no lists or multiple values)

**❌ VIOLATION in your database:**

**FETS_DOCUMENTS table:**
```
property_no = "FO11-AP7-20-0001,FO11-AR1-20-0001,FO11-CO1-18-0009"
```
☝️ This stores MULTIPLE property numbers in ONE cell!

**✅ HOW TO FIX:**
Create a separate table `fets_items`:
```
fets_items table:
- id
- fets_document_id (which FETS this belongs to)
- property_no (ONE property number per row)
```

**Before (NOT 1NF):**
```
FETS #1: property_no = "FO11-1,FO11-2,FO11-3"
```

**After (IS 1NF):**
```
FETS #1 has 3 rows in fets_items:
- fets_document_id=1, property_no="FO11-1"
- fets_document_id=1, property_no="FO11-2"
- fets_document_id=1, property_no="FO11-3"
```

---

### **2NF (Second Normal Form)** - "No Partial Dependencies"

**Rule**: Every non-key attribute must depend on the WHOLE primary key, not just part of it.

**This only matters if you have a COMPOSITE PRIMARY KEY** (key made of 2+ columns).

**✅ YOUR DATABASE**: Most tables use single `id` as primary key, so they automatically pass 2NF!

**⚠️ POTENTIAL ISSUE:**
If you create the `fets_items` table suggested above, make sure:
- Primary key is just `id`
- OR if using composite key `(fets_document_id, property_no)`, ensure all other columns depend on BOTH parts

---

### **3NF (Third Normal Form)** - "No Transitive Dependencies"

**Rule**: Non-key attributes should NOT depend on other non-key attributes.

**❌ VIOLATIONS in your database:**

#### **Problem 1: USERS table**
```
users table has:
- province (text: "Davao De Oro")
- municipality (text: "Davao City")  
- office (text: "Paquibato Sub-District")
```

**Why is this wrong?**
- If you change "Davao City" to "Davao City Municipality", you'd have to update MANY rows
- Province name is repeated for every user in that province
- No central list of provinces

**✅ FIX: Create separate location tables**

Already exists! `places` table:
```
places table:
- id
- type (province/municipality/office)
- parent_id (which location it belongs to)
- name
```

**Then change users table:**
```
users table:
- id
- fullname
- email
- office_id (foreign key to places table) ✅
```

This way:
- Province names stored ONCE
- Easy to update location names
- No duplication!

---

#### **Problem 2: INVENTORY table**
```
inventory table has:
- RECEIVER (text: "CABIDO-SOBRETODO, MARGIE")
```

**Why is this wrong?**
- Same person's name might be spelled differently in different rows
- If person's name changes (marriage, correction), update many rows
- No way to link to user account

**✅ FIX:**
```
inventory table:
- RECEIVER_ID (foreign key to users table) ✅
```

Store just the ID, get name from `users` table when needed!

---

#### **Problem 3: FETS_DOCUMENTS table**
```
fets_documents table has:
- verified_by (number: official_id)
- rejected_by (number: official_id)  
- approved_by (number: official_id)
```

**Current structure is OK!** ✅ These store IDs that link to `officials` table.

BUT could improve by being more explicit:
```
- verified_by_official_id ✅ (clearer name)
```

---

## 🎯 PART 3: NORMALIZATION ACTION PLAN

### Step 1: Fix 1NF Violation (Split comma-separated values)

**Create new table: `fets_items`**

```sql
CREATE TABLE fets_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    fets_document_id BIGINT NOT NULL,
    property_no VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (fets_document_id) REFERENCES fets_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (property_no) REFERENCES inventory(PROPERTY_NO)
);
```

**Migration steps:**
1. Create the new table
2. For each existing FETS document:
   - Split the `property_no` by commas
   - Insert each property as a separate row in `fets_items`
3. Remove `property_no` column from `fets_documents`

---

### Step 2: Fix 3NF Violations

#### **A. Normalize USERS location data**

**Already done partially!** You have `places` table.

**What to change:**

```sql
-- Add new column to users
ALTER TABLE users ADD COLUMN office_id BIGINT NULL;
ALTER TABLE users ADD FOREIGN KEY (office_id) REFERENCES places(id);

-- Then migrate data:
-- For each user, find their office in places table and update office_id

-- After migration, remove old columns:
ALTER TABLE users DROP COLUMN province;
ALTER TABLE users DROP COLUMN municipality;
ALTER TABLE users DROP COLUMN office;
```

---

#### **B. Normalize INVENTORY receiver data**

```sql
-- Add new column
ALTER TABLE inventory ADD COLUMN receiver_user_id BIGINT NULL;
ALTER TABLE inventory ADD FOREIGN KEY (receiver_user_id) REFERENCES users(id);

-- Migrate data:
-- Match RECEIVER text to users.fullname and update receiver_user_id

-- After migration, remove old column:
ALTER TABLE inventory DROP COLUMN RECEIVER;
```

---

## 📋 PART 4: BENEFITS AFTER NORMALIZATION

### Before (Current State):
```
FETS #380 has property_no = "FO11-AP7-20-0001,FO11-AR1-20-0001,FO11-CO1-18-0009"
```
- ❌ Can't easily query "Which FETS contains property FO11-AP7-20-0001?"
- ❌ Hard to validate each property exists
- ❌ Can't track individual item status in FETS

### After (Normalized):
```
fets_items table:
Row 1: fets_document_id=380, property_no="FO11-AP7-20-0001"
Row 2: fets_document_id=380, property_no="FO11-AR1-20-0001"  
Row 3: fets_document_id=380, property_no="FO11-CO1-18-0009"
```
- ✅ Easy to find all FETS containing a specific property
- ✅ Can enforce foreign key constraints
- ✅ Can add status per item (verified, approved, etc.)

---

### Before (Current):
```
User #25: province="Davao De Oro", office="Paquibato Sub-District"
User #26: province="Davao De Oro", office="Paquibato Sub-District"
...
(100 users, same text repeated 100 times!)
```
- ❌ If office name changes, update 100 rows
- ❌ Risk of typos ("Paquiabto" vs "Paquibato")
- ❌ Uses more disk space

### After (Normalized):
```
places table:
ID 17: name="Davao De Oro", type="province"
ID 52: name="Paquibato Sub-District", type="office", parent_id=2

users table:
User #25: office_id=52
User #26: office_id=52
...
```
- ✅ Change office name once, affects all users
- ✅ No typos possible
- ✅ Uses less space

---

## 🚀 PART 5: IMPLEMENTATION CHECKLIST

### ✅ Tasks for Your Instructor:

1. **Create `fets_items` table**
   - [ ] Write migration file
   - [ ] Create model `FetsItem.php`
   - [ ] Update relationships in `FetsDocument` model

2. **Migrate existing data**
   - [ ] Write script to split comma-separated property_no
   - [ ] Test with backup database first!

3. **Update code**
   - [ ] Change forms to use `fets_items` relationship
   - [ ] Update queries that use `property_no`
   - [ ] Update PDF generation

4. **Normalize users locations**
   - [ ] Add `office_id` column
   - [ ] Migrate province/municipality/office data
   - [ ] Update user forms

5. **Normalize inventory receivers**
   - [ ] Add `receiver_user_id` column  
   - [ ] Match receiver names to users
   - [ ] Update inventory displays

---

## 📖 PART 6: DATABASE DIAGRAM (Your Current Structure)

```
┌─────────────┐         ┌──────────────────┐         ┌────────────┐
│   USERS     │────────▶│ FETS_DOCUMENTS   │────────▶│  OFFICIALS │
│             │ user_id │                  │ verified│            │
│  - id       │         │  - id            │ _by     │  - id      │
│  - fullname │         │  - user_id       │         │  - role    │
│  - province │         │  - property_no   │         │  - fullname│
│  - office   │         │  - to_receiver   │         └────────────┘
└─────────────┘         │  - status        │
       │                │  - verified_by   │
       │                └──────────────────┘
       │                         │
       │                         │ (comma-separated!)
       │                         ▼
       │                ┌──────────────┐
       └───────────────▶│  INVENTORY   │
          RECEIVER      │              │
         (text name)    │  - PROPERTY_NO
                        │  - RECEIVER    │
                        │  - STATUS      │
                        └────────────────┘
```

## 📖 PART 7: RECOMMENDED NORMALIZED STRUCTURE

```
┌─────────────┐         ┌──────────────────┐         ┌────────────┐
│   USERS     │────────▶│ FETS_DOCUMENTS   │────────▶│  OFFICIALS │
│             │ user_id │                  │ verified│            │
│  - id       │         │  - id            │ _by     │  - id      │
│  - fullname │         │  - user_id       │         │  - role    │
│  - office_id├────┐    │  - to_receiver   │         │  - fullname│
└─────────────┘    │    │  - status        │         └────────────┘
                   │    │  - verified_by   │
┌─────────────┐    │    └──────────────────┘
│   PLACES    │◀───┘             │
│             │                  │ (one-to-many)
│  - id       │                  ▼
│  - name     │         ┌──────────────────┐
│  - type     │         │   FETS_ITEMS     │ ✨ NEW!
│  - parent_id│         │                  │
└─────────────┘         │  - id            │
                        │  - fets_doc_id   │
       ┌────────────────┤  - property_no   │
       │                └──────────────────┘
       │                         │
       │                         │ (foreign key)
       │                         ▼
       │                ┌──────────────┐
       └───────────────▶│  INVENTORY   │
          receiver_id   │              │
         (foreign key)  │  - PROPERTY_NO
                        │  - receiver_id │ ✨ CHANGED!
                        │  - STATUS      │
                        └────────────────┘
```

---

## 💡 SUMMARY FOR YOUR INSTRUCTOR

**Current Issues:**
1. ❌ **1NF Violation**: `fets_documents.property_no` stores multiple values
2. ❌ **3NF Violation**: `users` table stores province/office as text (duplicated)
3. ❌ **3NF Violation**: `inventory.RECEIVER` stores name as text (duplicated)

**Solutions:**
1. ✅ Create `fets_items` junction table
2. ✅ Use `places` table with `office_id` foreign key in users
3. ✅ Add `receiver_user_id` foreign key in inventory

**Benefits:**
- Easier queries
- Data consistency
- Better performance
- Follows database best practices

---

## 📚 GLOSSARY

- **Primary Key (PK)**: Unique identifier for each row (usually `id`)
- **Foreign Key (FK)**: Column that links to another table's primary key
- **Normalization**: Process of organizing database to reduce duplication
- **Denormalization**: Opposite - adding duplication for performance (advanced topic)
- **Junction Table**: Table that connects two other tables (like `fets_items`)
- **Relationship**: How tables are connected (one-to-many, many-to-many)

---

Good luck with your database normalization! 🎓✨
