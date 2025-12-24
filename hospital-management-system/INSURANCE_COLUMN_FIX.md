# Fix for Missing 'insurance_covered' Column Error

## Problem Description

The Hospital Management System is encountering a WordPress database error:

```
WordPress database error: [Unknown column 'insurance_covered' in 'field list']
SELECT id, bill_id, total_amount, paid_amount, insurance_covered, due_amount, 
(total_amount - COALESCE(paid_amount, 0) - COALESCE(insurance_covered, 0)) as calculated_due 
FROM wp_hms_bills WHERE total_amount > 0 HAVING ABS(calculated_due - due_amount) > 0.01 ORDER BY id DESC LIMIT 10
```

This error occurs because the `insurance_covered` column is missing from the `wp_hms_bills` table.

## Solution Overview

We've created a database migration to add the missing `insurance_covered` column to the bills table. The migration will:

1. Check if the column exists
2. Add the column with appropriate data type (DECIMAL(10,2) with default value 0.00)
3. Place it in the correct position in the table (after insurance_provider_id)

## Files Modified

1. **includes/class-hms-migration.php**
   - Added `migrate_bills_table_insurance_covered()` method
   - Added the migration to the run_migrations() method

2. **admin/run-migrations-simple.php**
   - Updated the migration list to include the new migration

## New Files Created

1. **verify-insurance-column.php**
   - A verification script to check if the column exists
   - Provides instructions on how to fix the issue if it doesn't
   - Can be accessed directly to verify the fix

2. **run-insurance-migration.php**
   - A standalone script to run just this specific migration
   - Can be used if the full migration runner is not accessible

## How to Apply the Fix

### Method 1: Using the Migration Runner (Recommended)

1. Log in to your WordPress admin dashboard
2. Navigate to **Hospital Management System** → **Run Migrations**
3. Review the migrations that will be applied
4. Click the **"Run Migrations Now"** button
5. Verify the migration completed successfully

### Method 2: Using the Verification Script

1. Access the `verify-insurance-column.php` script through your WordPress admin
2. The script will check if the column exists and provide instructions
3. Follow the on-screen instructions to run the migration

### Method 3: Manual SQL Execution (Advanced)

If you have direct database access, you can run this SQL query:

```sql
ALTER TABLE wp_hms_bills ADD COLUMN insurance_covered DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER insurance_provider_id;
```

## Verification

After applying the fix, you can verify that the column was added correctly by:

1. Running the `verify-insurance-column.php` script
2. Checking the database structure directly
3. Running the original failing query to ensure it now works

## Technical Details

- **Column Name**: `insurance_covered`
- **Data Type**: `DECIMAL(10,2)`
- **Default Value**: `0.00`
- **Nullable**: `NOT NULL`
- **Position**: After `insurance_provider_id` column

## Impact on Existing Data

This migration is safe for existing data:
- It only adds a new column with a default value
- No existing data is modified
- The migration can be run multiple times without issues

## Future Considerations

The billing system should now be able to:
- Track insurance-covered amounts for each bill
- Calculate due amounts accounting for insurance payments
- Generate accurate reports including insurance information

## Support

If you encounter any issues with this fix:
1. Check your WordPress error logs
2. Verify you have the necessary database permissions
3. Ensure your database user has ALTER TABLE privileges
4. Contact your system administrator if needed