<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration normalizes the existing database structure following 3NF principles:
     * - 1NF: No repeating groups (fets_items table for property relationships)
     * - 2NF: No partial dependencies (single primary keys)
     * - 3NF: No transitive dependencies (office_id instead of province/municipality/office text)
     * 
     * This migration is INCREMENTAL - it works with existing tables and adds normalization
     */
    public function up(): void
    {
        // ============================================
        // STEP 1: Add unique constraint to inventory PROPERTY_NO (required for FK)
        // ============================================
        if (Schema::hasTable('inventory')) {
            $dbConnection = Schema::getConnection();
            $indexes = $dbConnection->select("SHOW INDEXES FROM inventory WHERE Key_name LIKE '%PROPERTY_NO%' AND Non_unique = 0");
            
            if (empty($indexes)) {
                Schema::table('inventory', function (Blueprint $table) {
                    $table->unique('PROPERTY_NO');
                });
            }
        }
        
        // ============================================
        // STEP 2: Create new tables that don't exist yet
        // ============================================
        
        // Places table (only if missing - this was created by earlier migration)
        if (!Schema::hasTable('places')) {
            Schema::create('places', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['province', 'municipality', 'office'])->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->string('name', 150); // Province/Municipality/Office names (optimized from 255)
                $table->string('code', 20)->nullable(); // Optional code for offices
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                // Foreign key
                $table->foreign('parent_id')->references('id')->on('places')->onDelete('cascade');
                
                // Indexes for performance
                $table->index(['type', 'active']);
            });
        }

        // ============================================
        // 2. USERS TABLE (Normalized)
        // ============================================
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('fullname', 150); // Full name (optimized from 255)
            $table->string('username', 50)->unique(); // Username (optimized from 255)
            $table->string('email', 100)->unique(); // Email addresses rarely exceed 100 chars
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 60); // bcrypt hashes are 60 characters
            
            // Contact Information
            $table->string('contact_number', 20)->nullable(); // Phone numbers with country code
            
            // Employment Information
            $table->enum('employee_status', ['Contractual', 'Regular', 'MOA'])->nullable();
            $table->enum('region', ['Region XI'])->default('Region XI');
            
            // Location (NORMALIZED - Foreign Key to places table)
            $table->unsignedBigInteger('office_id')->nullable()->index();
            
            // Access Control
            $table->enum('access_level', ['Superadmin', 'Regional DPSC', 'Provincial DPSC', 'Employee'])->default('Employee')->index();
            
            // Account Status
            $table->boolean('active')->default(true)->index(); // Replaced 'activated' Yes/No with boolean
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_code', 6)->nullable(); // 2FA codes are 6 digits
            $table->timestamp('two_factor_expires_at')->nullable();
            
            // Security
            $table->unsignedTinyInteger('failed_attempts')->default(0); // Max 255 attempts is enough
            $table->timestamp('locked_until')->nullable();
            
            // Audit Fields
            $table->timestamp('archived_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Soft delete instead of 'deleted_status'

            // Foreign keys
            $table->foreign('office_id')->references('id')->on('places')->onDelete('set null');
            
            // Additional indexes
            $table->index(['active', 'access_level']);
            });
        }

        // ============================================
        // 3. INVENTORY TABLE (Normalized)
        // ============================================
        if (!Schema::hasTable('inventory')) {
            Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            
            // Property Identification
            $table->string('property_no', 30)->unique()->index(); // Property numbers (e.g., "FO11-AP7-20-0001")
            $table->string('serial_no', 100)->nullable()->index(); // Manufacturer serial number
            
            // Classification
            $table->string('fund_code', 20)->nullable(); // Fund codes are short
            $table->enum('property_status', ['Renewed', 'Issued', 'Returned', 'New'])->default('New')->index();
            
            // Description
            $table->string('article_description', 200); // Type of item (Chair, Computer, etc.)
            $table->text('general_description')->nullable(); // Detailed description can be long
            
            // PAR Information
            $table->string('par_no', 50)->nullable()->index(); // Property Acknowledgment Receipt number
            $table->date('par_date')->nullable();
            
            // Quantity and Cost
            $table->string('unit', 20)->default('unit'); // Unit of measurement (pcs, set, etc.)
            $table->unsignedInteger('qty')->default(1); // Quantity as integer
            $table->decimal('acquisition_cost', 12, 2)->nullable(); // Up to 999,999,999.99
            $table->date('acquisition_date')->nullable()->index();
            
            // Receiver/Custodian (NORMALIZED - Foreign Key to users table)
            $table->unsignedBigInteger('receiver_user_id')->nullable()->index();
            $table->string('issued_to', 150)->nullable(); // Can be different from receiver
            
            // Additional Information
            $table->string('subpar', 50)->nullable();
            $table->string('account_code', 30)->nullable();
            $table->string('warranty', 50)->nullable();
            $table->string('office', 100)->nullable(); // Office location of item
            $table->string('found_in_station', 100)->nullable();
            $table->enum('labelled', ['Yes', 'No'])->nullable();
            
            // Current Status
            $table->enum('status', [
                'Operational', 
                'For Repair', 
                'Unserviceable', 
                'Disposed',
                'Missing',
                'Under Repair'
            ])->default('Operational')->index();
            
            // Remarks and Metadata
            $table->text('dpo_remarks')->nullable(); // Property Officer's notes
            $table->string('source_file', 200)->nullable(); // Excel file source
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('receiver_user_id')->references('id')->on('users')->onDelete('set null');
            
            // Composite indexes for common queries
            $table->index(['property_status', 'status']);
            $table->index(['receiver_user_id', 'status']);
            });
        } else {
            // If inventory table already exists, ensure PROPERTY_NO is unique for FK constraint
            // Use raw SQL to add unique constraint only if it doesn't exist
            $dbConnection = Schema::getConnection();
            $indexes = $dbConnection->select("SHOW INDEXES FROM inventory WHERE Key_name LIKE '%PROPERTY_NO%' AND Non_unique = 0");
            
            if (empty($indexes)) {
                // Add unique constraint to PROPERTY_NO column (existing table uses uppercase)
                Schema::table('inventory', function (Blueprint $table) {
                    $table->unique('PROPERTY_NO');
                });
            }
        }

        // ============================================
        // 4. OFFICIALS TABLE (Signing Officials)
        // ============================================
        if (!Schema::hasTable('officials')) {
            Schema::create('officials', function (Blueprint $table) {
            $table->id();
            
            // Official Information
            $table->string('fullname', 150);
            $table->enum('role', [
                'Provincial DPSC',
                'Regional DPSC',
                'Head of Property',
                'Recommending Official',
                'Approving Official'
            ])->index();
            
            // Location (for Provincial DPSC)
            $table->string('province', 100)->nullable(); // Keep province as text for officials
            
            // Link to User Account (optional)
            $table->unsignedBigInteger('user_id')->nullable()->index();
            
            // Status
            $table->boolean('active')->default(true)->index();
            
            // Signature
            $table->text('signature_path')->nullable(); // Path to signature image
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Unique constraint: One active role per province
            $table->unique(['role', 'province', 'active'], 'unique_active_role_per_province');
            });
        }

        // ============================================
        // 5. OFFICIALS_HISTORY TABLE
        // ============================================
        if (!Schema::hasTable('officials_history')) {
            Schema::create('officials_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('official_id')->index();
            $table->string('fullname', 150);
            $table->string('role', 50);
            $table->string('province', 100)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('change_reason')->nullable();
            $table->timestamps();

            $table->foreign('official_id')->references('id')->on('officials')->onDelete('cascade');
            });
        }

        // ============================================
        // 6. REPAIR_DESTINATIONS TABLE
        // ============================================
        if (!Schema::hasTable('repair_destinations')) {
            Schema::create('repair_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200); // Repair shop/facility name
            $table->string('address', 250)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            });
        }

        // ============================================
        // 7. FETS_DOCUMENTS TABLE (Normalized)
        // ============================================
        if (!Schema::hasTable('fets_documents')) {
            Schema::create('fets_documents', function (Blueprint $table) {
            $table->id();
            
            // Document Information
            $table->string('document_number', 30)->nullable()->unique(); // Auto-generated document number
            
            // Transfer Details
            $table->string('to_receiver', 150); // Name of receiver
            $table->string('to_office', 150)->default('Pantawid (RPMO)');
            
            // Transfer Type
            $table->enum('transfer_movement', [
                'Issue/Transfer',
                'For Repair',
                'For Surrender',
                'Return to Lender'
            ])->default('Issue/Transfer')->index();
            
            // Repair Information (if applicable)
            $table->unsignedBigInteger('repair_destination_id')->nullable()->index();
            
            // Condition
            $table->enum('remarks', ['Serviceable', 'Unserviceable'])->default('Serviceable');
            
            // Workflow Status
            $table->enum('status', [
                'submitted',
                'verified',
                'approved',
                'rejected'
            ])->default('submitted')->index();
            
            // Document File
            $table->string('file_name', 200)->nullable();
            $table->string('file_path', 250)->nullable();
            
            // Workflow Actors
            $table->unsignedBigInteger('user_id')->index(); // Employee who created the FETS
            $table->unsignedBigInteger('verified_by_official_id')->nullable()->index();
            $table->unsignedBigInteger('approved_by_official_id')->nullable()->index();
            $table->unsignedBigInteger('rejected_by_official_id')->nullable()->index();
            
            // Workflow Timestamps
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            // Rejection Reason
            $table->text('rejected_remarks')->nullable();
            
            // Additional Data
            $table->json('form_data')->nullable(); // Additional form fields
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('repair_destination_id')->references('id')->on('repair_destinations')->onDelete('set null');
            $table->foreign('verified_by_official_id')->references('id')->on('officials')->onDelete('set null');
            $table->foreign('approved_by_official_id')->references('id')->on('officials')->onDelete('set null');
            $table->foreign('rejected_by_official_id')->references('id')->on('officials')->onDelete('set null');
            
            // Indexes for common queries
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            });
        }

        // ============================================
        // 8. FETS_ITEMS TABLE (NEW - Fixes 1NF Violation)
        // ============================================
        if (!Schema::hasTable('fets_items')) {
            Schema::create('fets_items', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->unsignedBigInteger('fets_document_id')->index();
            $table->string('property_no', 30)->index(); // One property per row (1NF compliant)
            
            // Item Status in FETS (optional)
            $table->enum('item_status', [
                'pending',
                'verified',
                'approved',
                'rejected'
            ])->default('pending');
            
            $table->text('item_remarks')->nullable(); // Remarks specific to this item
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('fets_document_id')->references('id')->on('fets_documents')->onDelete('cascade');
            // Reference PROPERTY_NO (uppercase) since existing table uses uppercase columns
            $table->foreign('property_no')->references('PROPERTY_NO')->on('inventory')->onDelete('cascade');
            
            // Unique constraint: Same property can't be in the same FETS document twice
            $table->unique(['fets_document_id', 'property_no'], 'unique_fets_property');
            
            // Composite index for queries
            $table->index(['fets_document_id', 'item_status']);
            });
        }

        // ============================================
        // 9. FETS_LOGS TABLE (Audit Trail)
        // ============================================
        if (!Schema::hasTable('fets_logs')) {
            Schema::create('fets_logs', function (Blueprint $table) {
            $table->id();
            
            // What was logged
            $table->unsignedBigInteger('fets_document_id')->index();
            $table->string('property_no', 30)->nullable(); // Can be null for document-level actions
            
            // Action Details
            $table->enum('action', [
                'created',
                'submitted',
                'verified',
                'approved',
                'rejected',
                'updated',
                'deleted'
            ])->index();
            
            // Who did it
            $table->string('actor', 150); // Name of person who performed action
            $table->string('actor_role', 50); // Their role
            $table->unsignedBigInteger('actor_user_id')->nullable(); // Link to user if available
            
            // Additional Information
            $table->text('remarks')->nullable();
            $table->json('changes')->nullable(); // Store what changed (before/after)
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('fets_document_id')->references('id')->on('fets_documents')->onDelete('cascade');
            
            // Indexes
            $table->index(['fets_document_id', 'created_at']);
            $table->index(['action', 'created_at']);
            });
        }

        // ============================================
        // 10. IMPORT_PROGRESS TABLE
        // ============================================
        if (!Schema::hasTable('import_progress')) {
            Schema::create('import_progress', function (Blueprint $table) {
            $table->id();
            
            // File Information
            $table->string('file_name', 200);
            $table->string('file_type', 20); // csv, xlsx, etc.
            $table->enum('import_type', ['users', 'inventory', 'officials'])->index();
            
            // Import Status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'partially_completed'
            ])->default('pending')->index();
            
            // Progress Tracking
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            
            // Results
            $table->json('errors')->nullable(); // Array of error messages
            $table->json('logs')->nullable(); // Detailed logs
            
            // User who initiated import
            $table->unsignedBigInteger('user_id')->index();
            
            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // ============================================
        // 11. MANUALS TABLE (User Documentation)
        // ============================================
        if (!Schema::hasTable('manuals')) {
            Schema::create('manuals', function (Blueprint $table) {
            $table->id();
            
            $table->string('filename', 200); // Stored filename
            $table->string('original_name', 200); // Display name
            $table->unsignedBigInteger('file_size'); // Size in bytes
            $table->string('file_type', 20)->default('pdf'); // pdf, docx, etc.
            $table->text('description')->nullable();
            
            // Version Control
            $table->string('version', 20)->default('1.0');
            $table->boolean('is_latest')->default(true);
            
            // Access Control
            $table->enum('visibility', ['public', 'employee', 'admin'])->default('employee');
            
            // Upload Information
            $table->unsignedBigInteger('uploaded_by')->index();
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // ============================================
        // 12. ACTIVITY_LOG TABLE (Spatie Activity Log)
        // ============================================
        if (!Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name', 50)->nullable()->index();
            $table->text('description');
            
            // Subject (what was affected)
            $table->nullableMorphs('subject', 'subject');
            
            // Event type
            $table->string('event', 50)->nullable()->index();
            
            // Causer (who did it)
            $table->nullableMorphs('causer', 'causer');
            
            // Properties (changes, attributes, etc.)
            $table->json('properties')->nullable();
            
            // Batch UUID for grouping related activities
            $table->uuid('batch_uuid')->nullable()->index();
            
            $table->timestamps();

            // Indexes for performance
            $table->index(['log_name', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order due to foreign key constraints
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('manuals');
        Schema::dropIfExists('import_progress');
        Schema::dropIfExists('fets_logs');
        Schema::dropIfExists('fets_items');
        Schema::dropIfExists('fets_documents');
        Schema::dropIfExists('repair_destinations');
        Schema::dropIfExists('officials_history');
        Schema::dropIfExists('officials');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('users');
        Schema::dropIfExists('places');
    }
};
