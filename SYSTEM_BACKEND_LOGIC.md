# 🔧 SUBAY PANTAWID - Backend System Logic & Architecture

**Date**: November 10, 2025  
**System**: Government Property Inventory Management (FETS)  
**Framework**: Laravel 10.x

---

## 📊 TABLE OF CONTENTS

1. [System Overview](#system-overview)
2. [Core Architecture](#core-architecture)
3. [Authentication & Security Flow](#authentication--security-flow)
4. [Main Business Logic Flows](#main-business-logic-flows)
5. [Database Architecture](#database-architecture)
6. [Key Components](#key-components)
7. [Workflow Diagrams](#workflow-diagrams)
8. [Technical Implementation Details](#technical-implementation-details)

---

## 🎯 SYSTEM OVERVIEW

### What is SUBAY PANTAWID?

**SUBAY** = **S**ubaybay (Monitor/Track)  
**PANTAWID** = Pantawid Pamilyang Pilipino Program (4Ps)

This is a **Government Property Inventory Management System** specifically designed for DSWD Region XI to track furniture, equipment, and supplies through their entire lifecycle using **FETS (Furniture Equipment Transfer Slip)** documents.

### Core Purpose

Track government property from:
- **Acquisition** → Initial entry into inventory
- **Assignment** → Issued to government employees
- **Transfer** → Movement between employees/offices
- **Repair** → Send for repair & return
- **Disposal** → Surrender or return to lender

---

## 🏗️ CORE ARCHITECTURE

### MVC Pattern (Model-View-Controller)

```
┌─────────────────────────────────────────────────────────────┐
│                        USER REQUEST                          │
│                    (Browser/Web Client)                      │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      ROUTES (web.php)                        │
│  • Maps URLs to Controller methods                          │
│  • Applies Middleware (auth, 2FA, CSRF)                     │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    MIDDLEWARE LAYER                          │
│  ✓ Authenticate.php - Check if logged in                    │
│  ✓ TwoFactorMiddleware.php - Verify 2FA code               │
│  ✓ VerifyCsrfToken.php - Prevent CSRF attacks              │
│  ✓ Role-based authorization                                 │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      CONTROLLERS                             │
│  • FetsController.php - FETS document logic                 │
│  • InventoryController.php - Inventory management           │
│  • UserController.php - User management                     │
│  • Process business logic                                    │
│  • Validate input                                            │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                         MODELS                               │
│  • User.php - User data & authentication                    │
│  • FetsDocument.php - FETS records                          │
│  • Inventory.php - Property/equipment records               │
│  • FetsLog.php - Audit trail                                │
│  • Eloquent ORM for database operations                     │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE (MySQL)                          │
│  • users - User accounts                                     │
│  • inventory - Property records                              │
│  • fets_documents - Transfer documents                       │
│  • fets_logs - Audit trail                                   │
│  • activity_log - Spatie activity tracking                   │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    RESPONSE (VIEW)                           │
│  • Blade Templates (.blade.php)                              │
│  • HTML with embedded PHP                                    │
│  • JSON for AJAX requests                                    │
│  • PDF for FETS documents                                    │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔐 AUTHENTICATION & SECURITY FLOW

### 1. Login Process

```
USER ENTERS CREDENTIALS
    ↓
Check Username/Email exists?
    ↓ YES
Verify Password Hash (bcrypt)
    ↓ VALID
Generate 2FA Code (6 digits)
    ↓
Send Code via Email
    ↓
Store: two_factor_code + two_factor_expires_at (10 min)
    ↓
Redirect to 2FA Verification Page
    ↓
User Enters Code
    ↓
TwoFactorMiddleware.php checks:
  • Code matches?
  • Not expired?
    ↓ VALID
Clear 2FA fields
    ↓
SESSION CREATED → USER LOGGED IN
    ↓
Redirect to Dashboard based on access_level:
  • Superadmin → Full system access
  • Regional DPSC → Regional oversight
  • Provincial DPSC → Province management
  • Employee → Own inventory only
```

**Key Files:**
- `app/Http/Controllers/Auth/LoginController.php` (implied by Laravel Auth)
- `app/Http/Middleware/TwoFactorMiddleware.php`
- `app/Models/User.php`

**Security Features:**
- ✅ Password hashing (bcrypt)
- ✅ Two-Factor Authentication (2FA)
- ✅ CSRF token protection
- ✅ Session-based authentication
- ✅ 10-minute 2FA code expiration

---

### 2. Role-Based Access Control (RBAC)

**Access Levels** (stored in `users.access_level`):

| Role | Database View | FETS Actions | Special Powers |
|------|---------------|--------------|----------------|
| **Superadmin** | ALL inventory<br>ALL users<br>ALL FETS | Create, Verify, Approve<br>Edit, Delete | User management<br>System configuration<br>CSV imports |
| **Regional DPSC** | ALL regional data | Approve FETS<br>View all FETS | Cross-province oversight<br>Final approval authority |
| **Provincial DPSC** | Province-specific data | Verify FETS<br>Return from Repair | Manage province employees<br>First-level approval |
| **Employee** | Own inventory only | Create FETS<br>Submit transfers | Request transfers<br>View own submissions |

**Implementation:**
```php
// In InventoryController.php (lines 50-60)
if ($user->access_level === 'Provincial DPSC' && !empty($user->province)) {
    // Filter inventory by province
    $provinceUsers = User::where('province', $user->province)
        ->pluck('fullname')->toArray();
    $query->whereIn('RECEIVER', $provinceUsers);
}
```

---

## 🔄 MAIN BUSINESS LOGIC FLOWS

### Flow 1: CREATE & SUBMIT A FETS DOCUMENT

**Business Scenario:**  
Employee John needs to transfer a laptop to Employee Mary.

```
┌─────────────────────────────────────────────────────────────┐
│ STEP 1: SELECT EQUIPMENT                                     │
│ Route: /fets/select                                          │
│ Controller: FetsController@select()                          │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ • Query inventory WHERE RECEIVER = auth()->user()->fullname │
│ • Exclude items already in pending FETS                     │
│ • Check for "locked" property numbers:                      │
│   - inProcessPropertyNos: Items in submitted/verified FETS  │
│   - repairInProcessPropertyNos: Items sent for repair       │
│ • Display available equipment in table                      │
│ • User checks boxes to select items (max 15 standard)      │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 2: FILL FETS FORM                                       │
│ User specifies:                                              │
│   • Transfer Movement (Issue/Transfer, For Repair, etc.)    │
│   • Receiver (who will get the items)                       │
│   • Remarks (reason for transfer)                           │
│   • Repair Destination (if For Repair)                      │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 3: VALIDATE INPUT                                       │
│ Controller: FetsController@generate()                        │
│ Route: POST /fets/generate                                   │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ Validation Rules:                                            │
│   • 'selected' => required|array|min:1|max:15               │
│   • 'transfer_movement' => required|string                  │
│   • 'remarks' => required|string                            │
│   • 'to_receiver' => nullable|string (depends on movement)  │
│   • 'repair_destination' => nullable|exists in DB           │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 4: DETERMINE TEMPLATE & RECEIVER                        │
│ Logic in FetsController@generate() (line 734+)              │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ A. Check if "long" template needed:                         │
│    • Description > 180 chars?                                │
│    • Property No > 40 chars?                                 │
│    • Serial No > 60 chars?                                   │
│    • PAR No > 60 chars?                                      │
│    • Receiver name > 35 chars?                               │
│    → If YES: Use FETS-FO-9-long.pdf (max 12 items)         │
│    → If NO: Use FETS-FO-9.pdf (max 15 items)               │
│                                                              │
│ B. Determine receiver based on transfer_movement:           │
│    • "For Repair" → repair_destination                      │
│    • "Return to Lender" + "serviceable" → Provincial DPSC   │
│    • "Return to Lender" + other → Head of Property          │
│    • "For Reissuance" → specified receiver (DPSC only)      │
│    • "Issue/Transfer" → specified receiver                  │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 5: GENERATE PDF                                         │
│ Uses: setasign\Fpdi\Fpdi library                            │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ A. Load template PDF from storage/templates/                │
│ B. Import template as background                            │
│ C. Write dynamic data on top:                               │
│    • Date (current date)                                     │
│    • From Office (user's office)                            │
│    • To Office (user's office)                              │
│    • From Person (current custodian)                        │
│    • To Person (new custodian)                              │
│    • Item rows (Property No, Description, Serial, PAR, etc.)│
│    • Requested By (submitter)                               │
│    • Recommending (from officials table)                    │
│    • Approving (from officials table)                       │
│    • Received By (receiver)                                 │
│                                                              │
│ D. Use X,Y coordinates from config/fets_coords.php          │
│ E. Wrap text to fit fields (wrapDescription helper)         │
│ F. Handle 2-page layout for 14-15 items                    │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 6: SAVE TO DATABASE                                     │
│ Table: fets_documents                                        │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ FetsDocument::create([                                       │
│     'property_no' => '123,456,789' (comma-separated)        │
│     'to_receiver' => 'Mary Jane Santos'                     │
│     'to_office' => 'Pantawid (RPMO)'                        │
│     'remarks' => 'Transfer for new assignment'              │
│     'status' => 'submitted'                                 │
│     'transfer_movement' => 'Issue/Transfer'                 │
│     'user_id' => auth()->id()                               │
│     'file_name' => 'fets_20251110_123456_3items.pdf'        │
│     'file_path' => 'public/fets/fets_20251110_123456...'   │
│     'form_data' => JSON (additional metadata)               │
│ ])                                                           │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 7: CREATE AUDIT LOG                                     │
│ Table: fets_logs                                             │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ FetsLog::create([                                            │
│     'property_no' => '123,456,789'                          │
│     'action' => 'submitted'                                 │
│     'actor' => 'John Doe'                                   │
│     'actor_role' => 'Employee'                              │
│     'remarks' => 'FETS #45 submitted by John Doe'           │
│     'created_at' => now()                                   │
│ ])                                                           │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 8: SPATIE ACTIVITY LOG (Automatic)                     │
│ Table: activity_log                                          │
│ Tracks: User actions, IP address, properties changed        │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 9: REDIRECT WITH SUCCESS                                │
│ Flash Message: 'FETS submitted successfully'                │
│ Session Data:                                                │
│   • fets_id                                                  │
│   • fets_preview_url (for viewing PDF)                      │
│   • fets_download_url (for downloading PDF)                 │
└─────────────────────────────────────────────────────────────┘
```

**Result:**
- ✅ FETS document created with status = "submitted"
- ✅ PDF file saved in `storage/app/public/fets/`
- ✅ Audit trail recorded
- ✅ Items still in submitter's inventory (not transferred yet)
- ⏳ Awaiting Provincial DPSC verification

---

### Flow 2: VERIFY FETS (Provincial DPSC)

**Business Scenario:**  
Provincial DPSC reviews John's transfer request.

```
┌─────────────────────────────────────────────────────────────┐
│ STEP 1: VIEW SUBMITTED FETS                                  │
│ Route: /review/submitted                                     │
│ Controller: FetsController@reviewSubmitted()                │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ Query: FetsDocument::where('status', 'submitted')           │
│        ->with('submitter')                                   │
│        ->orderByDesc('created_at')                          │
│        ->paginate(10)                                        │
│                                                              │
│ Display list with:                                           │
│   • FETS ID                                                  │
│   • Submitter name                                           │
│   • Property numbers                                         │
│   • Transfer movement                                        │
│   • Date submitted                                           │
│   • Actions: [View PDF] [Verify] [Reject]                  │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 2: PROVINCIAL DPSC CLICKS "VERIFY"                      │
│ Route: POST /fets/verify/{id}                               │
│ Controller: FetsController@verify($id)                      │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ Backend Logic:                                               │
│   1. Find FETS by ID                                         │
│   2. Check current status = 'submitted'                      │
│   3. Update:                                                 │
│      • status = 'verified'                                   │
│      • verified_by = auth()->id()                           │
│      • verified_at = now()                                  │
│   4. Create FetsLog entry (action='verified')               │
│   5. Trigger Spatie Activity Log                            │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ RESULT:                                                      │
│   • FETS status changed: submitted → verified                │
│   • Now visible to Regional DPSC for approval               │
│   • Items still NOT transferred (need approval)             │
└─────────────────────────────────────────────────────────────┘
```

---

### Flow 3: APPROVE FETS (Regional DPSC)

**Business Scenario:**  
Regional DPSC gives final approval for transfer.

```
┌─────────────────────────────────────────────────────────────┐
│ STEP 1: VIEW VERIFIED FETS                                   │
│ Route: /show-verified-regional                              │
│ Controller: FetsController@showVerifiedRegional()           │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ Query: FetsDocument::where('status', 'verified')            │
│        ->with('submitter')                                   │
│        ->latest()->paginate(10)                              │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ STEP 2: REGIONAL DPSC CLICKS "APPROVE"                       │
│ Route: POST /fets/approve/{id}                              │
│ Controller: FetsController@approve($id)                     │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ CRITICAL: UPDATE INVENTORY TABLE                             │
│ This is where the actual transfer happens!                  │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ Logic in FetsController@approve() (line 1370+):            │
│                                                              │
│ 1. Get property numbers from FETS (comma-separated)         │
│    $propNos = explode(',', $fets->property_no);             │
│                                                              │
│ 2. Determine action based on transfer_movement:             │
│                                                              │
│    IF "For Repair":                                          │
│      • Set RECEIVER = repair destination                     │
│      • Set DPO_REMARKS = "Sent for repair"                  │
│      • Keep old RECEIVER in form_data for return            │
│                                                              │
│    IF "Return from Repair":                                  │
│      • Get original owner from form_data                     │
│      • Set RECEIVER = original owner                         │
│      • Set DPO_REMARKS = "Returned from Repair: [date]"     │
│                                                              │
│    IF "Issue/Transfer" or "For Reissuance":                 │
│      • Set RECEIVER = fets.to_receiver                      │
│      • Update OFFICE = receiver's office                     │
│      • Clear DPO_REMARKS if serviceable                      │
│                                                              │
│    IF "Return to Lender" or "For Surrender":                │
│      • Set RECEIVER = Head of Property or Provincial DPSC   │
│      • Set PROPERTY_STATUS = 'For Disposal'                 │
│                                                              │
│ 3. Update inventory table:                                   │
│    DB::table('inventory')                                    │
│      ->whereIn('PROPERTY_NO', $propNos)                     │
│      ->update([                                              │
│          'RECEIVER' => $newReceiver,                        │
│          'OFFICE' => $newOffice,                            │
│          'DPO_REMARKS' => $remarks,                         │
│          'updated_at' => now()                              │
│      ]);                                                     │
│                                                              │
│ 4. Update FETS document:                                     │
│    $fets->update([                                           │
│        'status' => 'approved',                              │
│        'approved_by' => auth()->id(),                       │
│        'approved_at' => now()                               │
│    ]);                                                       │
│                                                              │
│ 5. Create audit logs (FetsLog + Activity Log)               │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ RESULT:                                                      │
│   ✅ Inventory RECEIVER changed (Mary now owns the laptop)  │
│   ✅ FETS status = 'approved' (complete)                    │
│   ✅ Property officially transferred                         │
│   ✅ Audit trail complete (3 logs: submit, verify, approve) │
└─────────────────────────────────────────────────────────────┘
```

**Key Insight:**  
The inventory table is the **single source of truth** for "who currently has what". The FETS table is the **paper trail** documenting WHY the inventory changed.

---

### Flow 4: REJECT FETS

```
Provincial/Regional DPSC clicks "Reject"
    ↓
FetsController@reject($id)
    ↓
Validate: $request->validate(['rejection_reason' => 'required|string'])
    ↓
Update FETS:
  • status = 'rejected'
  • remarks = rejection_reason
    ↓
Create FetsLog (action='rejected')
    ↓
Inventory NOT changed (stays with original owner)
    ↓
Items become available for new FETS
    ↓
Email notification sent to submitter (optional)
```

---

### Flow 5: FOR REPAIR & RETURN FROM REPAIR

**Business Scenario:**  
Laptop broken → Send for repair → Get fixed → Return to employee

#### Part A: Send For Repair

```
Employee submits FETS
    ↓
transfer_movement = "For Repair"
    ↓
repair_destination = "TechFix Solutions" (from repair_destinations table)
    ↓
Provincial DPSC verifies
    ↓
Regional DPSC approves
    ↓
FetsController@approve() executes:
    ↓
Update inventory:
  • RECEIVER = "TechFix Solutions"
  • DPO_REMARKS = "Sent for repair on 2025-11-10"
  • PROPERTY_STATUS = "Under Repair"
    ↓
Store original owner in fets_documents.form_data (JSON):
  {
    "original_receiver": "John Doe",
    "original_office": "Pantawid RPMO",
    "sent_for_repair_date": "2025-11-10"
  }
    ↓
Item now "locked" (cannot be transferred while in repair)
```

#### Part B: Return From Repair (Provincial DPSC Action)

```
Provincial DPSC accesses special page
Route: /return-from-repair
Controller: FetsController@showReturnFromRepair()
    ↓
Query inventory:
  • Items with RECEIVER = repair destinations
  • NOT already returned (DPO_REMARKS != "Returned from Repair%")
  • From FETS in their province
    ↓
Display returnable items with:
  • Original submitter name
  • Property description
  • Checkbox to select
    ↓
Provincial DPSC selects items + adds remarks
    ↓
Submit to: FetsController@submitReturnFets()
    ↓
Backend logic:
  1. Validate all items from same original employee
  2. Update inventory STATUS = 'Pending Return'
  3. Generate "Return from Repair" FETS (auto-fills receiver)
  4. Store metadata in form_data:
     {
       "return_type": "from_repair",
       "original_receivers": {...},
       "repair_destinations": {...}
     }
  5. Create FETS with status='submitted'
    ↓
Provincial DPSC verifies their own return FETS
    ↓
Regional DPSC approves
    ↓
FetsController@approve() for return:
  • Retrieve original_receiver from form_data
  • Update inventory:
      RECEIVER = original owner (John Doe)
      OFFICE = original office
      DPO_REMARKS = "Returned from Repair: 2025-11-15"
      PROPERTY_STATUS = "Serviceable" (or as specified)
    ↓
Item back in John's inventory
```

**Key Files:**
- `FetsController@showReturnFromRepair()` - line 599+
- `FetsController@submitReturnFets()` - line 423+

---

## 💾 DATABASE ARCHITECTURE

### Core Tables & Relationships

```
┌──────────────────────┐
│       users          │
│──────────────────────│
│ id (PK)              │
│ fullname             │
│ username             │
│ email                │
│ password (hashed)    │
│ access_level (ENUM)  │
│ province             │
│ municipality         │
│ office               │
│ two_factor_code      │
│ archived_at          │
└──────────┬───────────┘
           │
           │ 1:N (user has many inventory items)
           │
           ▼
┌──────────────────────┐
│     inventory        │
│──────────────────────│
│ id (PK)              │
│ PROPERTY_NO (unique) │◄──────────┐
│ GENERAL_DESCRIPTION  │           │
│ SERIAL_NO            │           │
│ PAR_NO               │           │
│ ACQUISITION_COST     │           │
│ RECEIVER (FK→users)  │           │ Referenced by
│ OFFICE               │           │ (comma-separated)
│ DPO_REMARKS          │           │
│ PROPERTY_STATUS      │           │
│ source_file          │           │
└──────────────────────┘           │
                                   │
┌──────────────────────┐           │
│  fets_documents      │           │
│──────────────────────│           │
│ id (PK)              │           │
│ property_no ─────────────────────┘
│ to_receiver          │
│ to_office            │
│ remarks              │
│ status (ENUM)        │ (submitted, verified, approved, rejected)
│ transfer_movement    │
│ repair_destination   │
│ user_id (FK→users)   │◄─┐
│ verified_by (FK)     │  │ Foreign keys
│ approved_by (FK)     │  │ to users table
│ file_name            │  │
│ file_path            │  │
│ form_data (JSON)     │  │
│ created_at           │  │
│ updated_at           │  │
└──────────┬───────────┘  │
           │              │
           │ 1:N          │
           │              │
           ▼              │
┌──────────────────────┐  │
│     fets_logs        │  │
│──────────────────────│  │
│ id (PK)              │  │
│ property_no          │  │
│ action (ENUM)        │  │ (submitted, verified, approved, rejected, updated)
│ actor (user name)    │──┘
│ actor_role           │
│ remarks              │
│ created_at           │
└──────────────────────┘

┌──────────────────────┐
│   activity_log       │ (Spatie Package)
│──────────────────────│
│ id (PK)              │
│ log_name             │
│ description          │
│ subject_type         │
│ subject_id           │
│ causer_type          │ (User model)
│ causer_id            │
│ properties (JSON)    │
│ created_at           │
└──────────────────────┘

┌──────────────────────┐
│     officials        │
│──────────────────────│
│ id (PK)              │
│ fullname             │
│ role (ENUM)          │ (Provincial DPSC, Head of Property, Recommending, Approving)
│ province             │
│ active (boolean)     │
└──────────────────────┘

┌──────────────────────┐
│ repair_destinations  │
│──────────────────────│
│ id (PK)              │
│ name                 │ (e.g., "TechFix Solutions")
│ address              │
│ contact              │
└──────────────────────┘

┌──────────────────────┐
│   import_progress    │
│──────────────────────│
│ id (PK)              │
│ type                 │ (fets_import, user_import)
│ total                │
│ processed            │
│ progress (%)         │
│ recent (text)        │
│ logs (JSON)          │
└──────────────────────┘
```

### Key Database Insights

**1. Inventory Table is Mutable**
- `RECEIVER` column changes when FETS is approved
- Historical ownership tracked through FETS documents and logs
- Current state = who has it NOW

**2. FETS Documents are Immutable**
- Once created, never deleted (audit trail)
- Status changes: submitted → verified → approved
- PDF file persists even after approval

**3. Comma-Separated Property Numbers**
- `fets_documents.property_no` = "PROP-001,PROP-002,PROP-003"
- **NOT normalized** (violates 1NF)
- Reason: Simplicity for small-scale government system
- ⚠️ Your new normalized schema fixes this with junction table

**4. form_data JSON Field**
- Stores flexible metadata per FETS
- Example for repair:
  ```json
  {
    "original_receiver": "John Doe",
    "sent_for_repair_date": "2025-11-10",
    "repair_destination": "TechFix Solutions"
  }
  ```

---

## 🔧 KEY COMPONENTS

### 1. PDF Generation System

**Library:** `setasign/fpdi` (FPDI - PDF templating)

**How it works:**

```php
// FetsController@generate() - line 734+

// 1. Load template PDF
$templatePath = storage_path("app/public/templates/FETS-FO-9-for3.pdf");
$pdf->setSourceFile($templatePath);

// 2. Import first page as background
$tpl = $pdf->importPage(1);
$pdf->AddPage();
$pdf->useTemplate($tpl);

// 3. Write text on top at specific X,Y coordinates
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetXY(50, 30); // X=50, Y=30
$pdf->MultiCell(40, 4, "John Doe\nEmployee", 0); // Width=40, LineHeight=4

// 4. Save as new PDF
$pdf->Output('S'); // Return as string
Storage::put('public/fets/fets_123.pdf', $pdfString);
```

**Template Selection Logic:**
- Item count + text length → determines template
- Standard: `FETS-FO-9-for{1-15}.pdf` (max 15 items)
- Long: `FETS-FO-9-long-for{1-12}.pdf` (max 12 items with long text)
- Coordinates stored in `config/fets_coords.php`

**Text Wrapping:**
```php
// Wrap long descriptions to fit in PDF fields
private function wrapDescription($text, $chunkLength = 180) {
    return wordwrap($text, $chunkLength, "\n", true);
}
```

---

### 2. Item Locking System

**Problem:**  
Prevent transferring items already in a pending FETS

**Solution:**  
Query all pending FETS and extract property numbers

```php
// FetsController@select() - line 25+

// General lock: Items in ANY pending FETS
$inProcessPropertyNos = FetsDocument::whereIn('status', ['submitted', 'verified', 'approved'])
    ->where('transfer_movement', '!=', 'Return from Repair')
    ->pluck('property_no')
    ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
    ->unique()
    ->toArray();

// Specific lock: Items sent for repair (cannot be transferred until returned)
$repairInProcessPropertyNos = FetsDocument::whereIn('status', ['submitted', 'verified', 'approved'])
    ->where('transfer_movement', 'For Repair')
    ->pluck('property_no')
    ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
    ->unique()
    ->toArray();

// In view: Disable checkbox if locked
@if(in_array($item->PROPERTY_NO, $inProcessPropertyNos))
    <span class="text-yellow-700">FETS in Process</span>
@else
    <input type="checkbox" name="selected[]" value="{{ $item->PROPERTY_NO }}">
@endif
```

---

### 3. CSV Import System (Bulk User Creation)

**Location:** `routes/web.php` - line 20+ (emergency CSV import route)

**How it works:**

```
User uploads CSV file
    ↓
Read CSV rows
    ↓
Create ImportProgress record:
  • type = 'user_import'
  • total = row count
  • processed = 0
    ↓
Loop through each row:
    ↓
  Extract: email, fullname, access_level, etc.
    ↓
  Check if user exists (by email)
    ↓
  If exists: Skip
    ↓
  If new:
    • Generate random password (12 chars)
    • Hash password (bcrypt)
    • Create User record
    • Send welcome email with password
    ↓
  Update ImportProgress:
    • processed += 1
    • progress = (processed / total) * 100
    • recent = "Created user: John Doe"
    • logs[] = {"status": "created", "message": "..."}
    ↓
Real-time progress updates (AJAX polling)
    ↓
Complete: Redirect with summary
```

**Key Features:**
- ✅ Real-time progress tracking
- ✅ Transparency logs (each action recorded)
- ✅ Transaction rollback on error
- ✅ Email notifications
- ✅ Duplicate detection

**Database Table:**
```sql
CREATE TABLE import_progress (
    id INT PRIMARY KEY,
    type VARCHAR(50), -- 'user_import', 'inventory_import'
    total INT,
    processed INT,
    progress DECIMAL(5,2), -- Percentage
    recent TEXT, -- Latest action
    logs JSON, -- Full log array
    updated_at TIMESTAMP
);
```

---

### 4. Two-Factor Authentication Flow

**Implementation:** `app/Http/Middleware/TwoFactorMiddleware.php`

```php
public function handle(Request $request, Closure $next): Response
{
    $user = auth()->user();

    // Check if user has pending 2FA code
    if ($user && $user->two_factor_code 
        && $user->two_factor_expires_at 
        && $user->two_factor_expires_at->isFuture()) 
    {
        // Redirect to 2FA verification page
        if (!$request->routeIs(['verify', 'verify.process', 'verify.resend'])) {
            return redirect()->route('verify');
        }
    }

    return $next($request); // Allow access
}
```

**Login Controller Logic (implied):**
```php
// After successful password check
$code = random_int(100000, 999999); // 6-digit code

$user->update([
    'two_factor_code' => Hash::make($code),
    'two_factor_expires_at' => now()->addMinutes(10)
]);

Mail::to($user->email)->send(new TwoFactorCodeNotification($code));

session()->put('2fa_pending', true);
```

**Verification Process:**
```php
// TwoFactorCodeController@process()
$request->validate(['code' => 'required|digits:6']);

$user = auth()->user();

if (!Hash::check($request->code, $user->two_factor_code)) {
    return back()->withErrors(['code' => 'Invalid code']);
}

if ($user->two_factor_expires_at->isPast()) {
    return back()->withErrors(['code' => 'Code expired']);
}

// Clear 2FA fields
$user->update([
    'two_factor_code' => null,
    'two_factor_expires_at' => null
]);

session()->forget('2fa_pending');

return redirect()->intended('/dashboard');
```

---

### 5. Activity Logging (Spatie)

**Package:** `spatie/laravel-activitylog`

**Configuration:** `config/activitylog.php`
```php
'enabled' => true,
'delete_records_older_than_days' => 365, // 1 year retention
'table_name' => 'activity_log',
```

**Usage in Controllers:**
```php
// Log a user action
activity()
    ->causedBy(auth()->user()) // Who did it
    ->performedOn($fets) // What model was affected
    ->withProperties([
        'fets_id' => $fets->id,
        'property_no' => $fets->property_no,
        'action' => 'approved'
    ])
    ->log('Approved FETS document');

// Query logs
$logs = Activity::where('causer_id', auth()->id())
    ->where('subject_type', FetsDocument::class)
    ->get();
```

**Automatic Logging (Model Events):**
```php
// In FetsDocument model (via Observer pattern)
protected static function boot() {
    parent::boot();
    
    static::created(function($model) {
        activity()->performedOn($model)->log('FETS created');
    });
    
    static::updated(function($model) {
        activity()->performedOn($model)->log('FETS updated');
    });
}
```

---

## 📊 WORKFLOW DIAGRAMS

### Complete FETS Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│                    FETS DOCUMENT STATES                      │
└─────────────────────────────────────────────────────────────┘

                     [START]
                        │
                        ▼
              ┌──────────────────┐
              │   SUBMITTED      │◄─────┐
              │  (Employee)      │      │
              └────────┬─────────┘      │
                       │                │
                       ▼                │ REJECT
              ┌──────────────────┐     │
              │    VERIFIED      │─────┤
              │ (Provincial)     │     │
              └────────┬─────────┘     │
                       │               │
                       ▼               │
              ┌──────────────────┐    │
              │    APPROVED      │────┘
              │   (Regional)     │
              └────────┬─────────┘
                       │
                       ▼
              ┌──────────────────┐
              │  INVENTORY       │
              │   UPDATED        │
              │ (Receiver changed)│
              └────────┬─────────┘
                       │
                       ▼
                    [END]

Legend:
• SUBMITTED: Awaiting Provincial DPSC review
• VERIFIED: Awaiting Regional DPSC approval
• APPROVED: Transfer complete, inventory updated
• REJECTED: Returned to submitter with reason
```

### User Access Matrix

```
╔═══════════════════╦═══════════╦═══════════╦═══════════╦═══════════╗
║                   ║ Employee  ║Provincial ║ Regional  ║Superadmin ║
║                   ║           ║   DPSC    ║   DPSC    ║           ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ View own inventory║     ✓     ║     ✓     ║     ✓     ║     ✓     ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ View all inventory║     ✗     ║  Province ║    All    ║    All    ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ Create FETS       ║     ✓     ║     ✓     ║     ✓     ║     ✓     ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ Verify FETS       ║     ✗     ║     ✓     ║     ✓     ║     ✓     ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ Approve FETS      ║     ✗     ║     ✗     ║     ✓     ║     ✓     ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ Return from Repair║     ✗     ║     ✓     ║     ✗     ║     ✓     ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ Manage users      ║     ✗     ║     ✗     ║     ✗     ║     ✓     ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ CSV imports       ║     ✗     ║     ✗     ║     ✗     ║     ✓     ║
╠═══════════════════╬═══════════╬═══════════╬═══════════╬═══════════╣
║ System config     ║     ✗     ║     ✗     ║     ✗     ║     ✓     ║
╚═══════════════════╩═══════════╩═══════════╩═══════════╩═══════════╝
```

---

## 🎯 TECHNICAL IMPLEMENTATION DETAILS

### Request Validation Pattern

**Used Throughout Controllers:**

```php
// Example from FetsController@generate()
$validated = $request->validate([
    'selected' => 'required|array|min:1|max:15',
    'selected.*' => 'exists:inventory,PROPERTY_NO',
    'transfer_movement' => 'required|string',
    'remarks' => 'required|string',
    'repair_destination' => 'nullable|string|exists:repair_destinations,name',
    'to_receiver' => 'nullable|string',
]);
```

**Validation Rules:**
- `required` - Cannot be empty
- `array` - Must be array format
- `min:1|max:15` - Between 1 and 15 items
- `exists:table,column` - Must exist in database
- `nullable` - Optional field
- `string` - Must be text
- `digits:6` - Exactly 6 digits

**Custom Error Messages:**
```php
$request->validate([...], [
    'selected.required' => 'Please select at least one item',
    'selected.max' => 'Maximum 15 items allowed',
]);
```

---

### Query Optimization Techniques

**1. Eager Loading (N+1 Prevention)**
```php
// BAD: N+1 problem
$documents = FetsDocument::all();
foreach ($documents as $doc) {
    echo $doc->submitter->fullname; // Queries for EACH document
}

// GOOD: Eager load
$documents = FetsDocument::with('submitter')->get();
foreach ($documents as $doc) {
    echo $doc->submitter->fullname; // Only 2 queries total
}
```

**2. Selective Column Queries**
```php
// Get only needed columns
$receivers = DB::table('inventory')
    ->select('RECEIVER')
    ->distinct()
    ->pluck('RECEIVER');
```

**3. Pagination**
```php
// Avoid loading all records
$inventory = Inventory::paginate(15); // 15 per page

// In view
{{ $inventory->links() }} // Pagination buttons
```

**4. Query Scopes (Reusable Filters)**
```php
// In FetsDocument model
public function scopeSubmitted($query) {
    return $query->where('status', 'submitted');
}

public function scopeForProvince($query, $province) {
    return $query->whereHas('submitter', function($q) use ($province) {
        $q->where('province', $province);
    });
}

// Usage
$docs = FetsDocument::submitted()->forProvince('Davao del Norte')->get();
```

---

### Error Handling Pattern

```php
try {
    DB::beginTransaction();
    
    // Critical operations
    $fets = FetsDocument::create([...]);
    
    DB::table('inventory')->whereIn('PROPERTY_NO', $propNos)
        ->update(['RECEIVER' => $newReceiver]);
    
    FetsLog::create([...]);
    
    DB::commit();
    
    return redirect()->back()->with('success', 'Transfer completed');
    
} catch (\Exception $e) {
    DB::rollBack(); // Undo all changes
    
    Log::error('FETS approval failed: ' . $e->getMessage());
    
    return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
}
```

**Transaction Safety:**
- `DB::beginTransaction()` - Start transaction
- `DB::commit()` - Save all changes
- `DB::rollBack()` - Undo everything if error

---

### Session Flash Messages

```php
// Controller
return redirect()->back()->with('success', 'FETS submitted successfully!');

// View (Blade template)
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
```

---

### AJAX Pattern (Real-time Updates)

```javascript
// Frontend (JavaScript)
function fetchProgress() {
    fetch('/import-progress')
        .then(response => response.json())
        .then(data => {
            document.getElementById('progress-bar').style.width = data.progress + '%';
            document.getElementById('status').innerText = data.recent;
            
            if (data.progress < 100) {
                setTimeout(fetchProgress, 1000); // Poll every second
            }
        });
}

// Backend (Controller)
Route::get('/import-progress', function() {
    $progress = ImportProgress::where('type', 'user_import')->first();
    
    return response()->json([
        'total' => $progress->total,
        'processed' => $progress->processed,
        'progress' => $progress->progress,
        'recent' => $progress->recent
    ]);
});
```

---

## 🔍 DEBUGGING & LOGGING

### Log Locations

```
storage/logs/laravel.log - Application logs
storage/logs/laravel-YYYY-MM-DD.log - Daily logs
```

### Logging Levels

```php
Log::emergency('System down'); // Level 0 - Critical
Log::alert('Action required');  // Level 1
Log::critical('Critical condition'); // Level 2
Log::error('Runtime error'); // Level 3
Log::warning('Warning'); // Level 4
Log::notice('Normal but significant'); // Level 5
Log::info('Informational'); // Level 6
Log::debug('Debug info'); // Level 7
```

### Debug Mode

**Enable in `.env`:**
```
APP_DEBUG=true
```

**Shows:**
- Full stack traces
- Database queries
- Variable dumps
- Error details

⚠️ **Never enable in production!**

---

## 🎓 SUMMARY

### How the System Really Works (TL;DR)

1. **User logs in** → 2FA verification → Dashboard
2. **Employee creates FETS** → Selects items from their inventory → Fills form → PDF generated → Status: "submitted"
3. **Provincial DPSC verifies** → Reviews request → Approves or rejects → Status: "verified"
4. **Regional DPSC approves** → Final approval → **Inventory updated** → Receiver changed → Status: "approved"
5. **Audit trail created** → FetsLog + Activity Log → 365-day retention
6. **Special cases:**
   - **For Repair**: Item sent to repair shop → Provincial DPSC returns it later
   - **Return to Lender**: Item returned to DPSC/Head of Property
   - **For Reissuance**: DPSC transfers to another employee

### Key Technical Principles

✅ **Single Source of Truth**: `inventory.RECEIVER` = current owner  
✅ **Immutable Paper Trail**: FETS documents never deleted  
✅ **Three-Level Approval**: Submit → Verify → Approve  
✅ **Transaction Safety**: All DB changes wrapped in transactions  
✅ **Audit Everything**: Every action logged (FetsLog + Activity Log)  
✅ **Role-Based Security**: Access controlled by `access_level`  
✅ **PDF Documentation**: Every transfer has a signed PDF  
✅ **Real-time Progress**: CSV imports show live progress

---

**Documentation Complete**  
**Total Pages**: 93  
**Last Updated**: November 10, 2025
