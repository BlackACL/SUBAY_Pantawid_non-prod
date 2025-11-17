<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration:
     * 1. Migrates data from fets_documents.property_no to fets_items table
     * 2. Removes the old property_no column from fets_documents
     * 3. Optimizes column sizes across the database
     */
    public function up(): void
    {
        echo "Starting data migration and optimization..." . PHP_EOL;
        
        // ============================================
        // STEP 1: Migrate existing FETS data to junction table
        // ============================================
        
        // Check if property_no column still exists
        $hasPropertyNoColumn = Schema::hasColumn('fets_documents', 'property_no');
        
        if ($hasPropertyNoColumn) {
            echo "Migrating FETS property data to junction table..." . PHP_EOL;
            
            $fetsDocuments = DB::table('fets_documents')
                ->whereNotNull('property_no')
                ->where('property_no', '!=', '')
                ->get();
        
        $migratedCount = 0;
        $skippedCount = 0;
        
        foreach ($fetsDocuments as $fets) {
            // Split comma-separated property numbers
            $propertyNumbers = array_filter(array_map('trim', explode(',', $fets->property_no)));
            
            foreach ($propertyNumbers as $propertyNo) {
                // Check if this property exists in inventory
                $exists = DB::table('inventory')
                    ->where('PROPERTY_NO', $propertyNo)
                    ->exists();
                
                if (!$exists) {
                    echo "  WARNING: Property {$propertyNo} not found in inventory (FETS #{$fets->id})" . PHP_EOL;
                    $skippedCount++;
                    continue;
                }
                
                // Check if already migrated
                $alreadyExists = DB::table('fets_items')
                    ->where('fets_document_id', $fets->id)
                    ->where('property_no', $propertyNo)
                    ->exists();
                
                if ($alreadyExists) {
                    continue;
                }
                
                // Map document status to item status
                $itemStatus = 'pending';
                if (isset($fets->status)) {
                    $statusMap = [
                        'submitted' => 'pending',
                        'verified' => 'verified',
                        'approved' => 'approved',
                        'rejected' => 'rejected',
                    ];
                    $itemStatus = $statusMap[strtolower($fets->status)] ?? 'pending';
                }
                
                // Insert into junction table
                DB::table('fets_items')->insert([
                    'fets_document_id' => $fets->id,
                    'property_no' => $propertyNo,
                    'item_status' => $itemStatus,
                    'item_remarks' => null,
                    'created_at' => $fets->created_at ?? now(),
                    'updated_at' => $fets->updated_at ?? now(),
                ]);
                
                $migratedCount++;
            }
        }
        
            echo "  ✓ Migrated {$migratedCount} property items" . PHP_EOL;
            if ($skippedCount > 0) {
                echo "  ⚠ Skipped {$skippedCount} items (property not found in inventory)" . PHP_EOL;
            }
        } else {
            echo "  ✓ Property data already migrated (property_no column not found)" . PHP_EOL;
        }
        
        // ============================================
        // STEP 2: Remove old property_no column from fets_documents
        // ============================================
        if ($hasPropertyNoColumn) {
            echo "Removing old property_no column from fets_documents..." . PHP_EOL;
            
            Schema::table('fets_documents', function (Blueprint $table) {
                $table->dropColumn('property_no');
            });
            
            echo "  ✓ Column removed" . PHP_EOL;
        } else {
            echo "  ✓ Property_no column already removed" . PHP_EOL;
        }
        
        // ============================================
        // STEP 3: Optimize column sizes in fets_documents
        // ============================================
        echo "Optimizing fets_documents column sizes..." . PHP_EOL;
        
        Schema::table('fets_documents', function (Blueprint $table) {
            // Optimize string column sizes
            $table->string('to_receiver', 150)->change();
            $table->string('to_office', 150)->change();
            $table->string('file_name', 200)->nullable()->change();
            $table->string('file_path', 250)->nullable()->change();
            $table->string('transfer_movement', 50)->nullable()->change();
            $table->string('repair_destination', 200)->nullable()->change();
            $table->string('remarks', 50)->nullable()->change(); // Keep as VARCHAR, not ENUM
            
            // Convert status to ENUM
            $table->enum('status', ['submitted', 'verified', 'approved', 'rejected'])->default('submitted')->change();
        });
        
        echo "  ✓ Columns optimized" . PHP_EOL;
        
        // ============================================
        // STEP 4: Optimize users table columns
        // ============================================
        if (Schema::hasTable('users')) {
            echo "Optimizing users table column sizes..." . PHP_EOL;
            
            Schema::table('users', function (Blueprint $table) {
                $table->string('fullname', 150)->change();
                $table->string('username', 50)->change();
                $table->string('email', 100)->change();
                $table->string('company_id', 10)->nullable()->change();
                $table->string('office', 150)->nullable()->change();
                $table->string('region', 50)->nullable()->change();
                $table->string('province', 100)->nullable()->change();
                $table->string('municipality', 100)->nullable()->change();
                $table->string('two_factor_code', 6)->nullable()->change();
            });
            
            echo "  ✓ Users table optimized" . PHP_EOL;
        }
        
        // ============================================
        // STEP 5: Skip inventory optimization (data too varied)
        // ============================================
        echo "Skipping inventory column optimization (existing data varies too much)" . PHP_EOL;
        echo "  Note: Column sizes can be optimized manually later if needed" . PHP_EOL;
        
        echo PHP_EOL . "✅ Migration and optimization completed successfully!" . PHP_EOL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "Reversing migration..." . PHP_EOL;
        
        // Add back property_no column to fets_documents
        Schema::table('fets_documents', function (Blueprint $table) {
            $table->string('property_no')->nullable()->after('id');
        });
        
        // Restore property_no data from junction table (comma-separated)
        $fetsDocuments = DB::table('fets_items')
            ->select('fets_document_id', DB::raw('GROUP_CONCAT(property_no) as property_nos'))
            ->groupBy('fets_document_id')
            ->get();
        
        foreach ($fetsDocuments as $fets) {
            DB::table('fets_documents')
                ->where('id', $fets->fets_document_id)
                ->update(['property_no' => $fets->property_nos]);
        }
        
        echo "✓ Rollback completed" . PHP_EOL;
    }
};
