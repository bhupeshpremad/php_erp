# PHP ERP3 Setup Guide

This project combines the updated modules from `php_erp` with the live data from `pup_erp2` to create a new, unified system.

## What's Included

✅ **Updated Modules**: All the latest code and features from `php_erp`  
✅ **Live Data**: All existing data from `pup_erp2` database  
✅ **Uploaded Files**: All images and documents from `pup_erp2/uploads`  
✅ **Schema Updates**: Latest database structure with new tables and columns  

## Quick Setup

### Option 1: Automatic Setup (Recommended)

1. **Open your browser** and navigate to:
   ```
   http://localhost/Comparing/php_erp3/import_database.php
   ```

2. **Click "Import Database"** - This will:
   - Create a new database called `php_erp3_db`
   - Import all live data from `pup_erp2`
   - Apply all schema updates from `php_erp`
   - Set up the system ready to use

3. **Access your new system**:
   ```
   http://localhost/Comparing/php_erp3/
   ```

### Option 2: Manual Setup

If you prefer to set up manually:

1. **Create Database**:
   ```sql
   CREATE DATABASE php_erp3_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import the combined SQL file**:
   - Use phpMyAdmin or MySQL command line
   - Import: `php_erp3_combined.sql`

3. **Update configuration** (already done):
   - Database name: `php_erp3_db`
   - Base URL: `http://localhost/Comparing/php_erp3/`

## Project Structure

```
php_erp3/
├── config/
│   └── config.php          # Updated for php_erp3_db
├── modules/                # All updated modules from php_erp
├── uploads/                # All files copied from pup_erp2
├── migrations/             # Database migration files
├── import_database.php     # Database setup script
├── php_erp3_combined.sql   # Combined SQL file
└── README_SETUP.md         # This file
```

## Key Differences from Original Projects

### From php_erp (Updated Code):
- Latest module implementations
- New migration files (019-046)
- Enhanced security features
- Improved database structure

### From pup_erp2 (Live Data):
- All existing business data
- User accounts and permissions
- Uploaded files and images
- Transaction history

### New in php_erp3:
- Combined the best of both projects
- Updated database schema
- Preserved all live data
- Ready for production use

## Database Configuration

The system is configured to use:
- **Database**: `php_erp3_db`
- **Host**: `localhost`
- **User**: `root` (or your MySQL user)
- **Password**: `` (or your MySQL password)

## Login Credentials

Use your existing credentials from the live system:
- **Email**: `accounts@thepurewood.com`
- **Password**: Your existing password

## Troubleshooting

### Database Connection Issues
1. Make sure XAMPP/MySQL is running
2. Check database credentials in `config/config.php`
3. Ensure `php_erp3_db` database exists

### Missing Data
1. Verify the import completed successfully
2. Check that `pup_erp2/u404997496_crm_purewood.sql` exists
3. Re-run the import process

### File Upload Issues
1. Check that `uploads/` folder has proper permissions
2. Verify files were copied from `pup_erp2/uploads/`

## Migration Details

The following migrations from `php_erp` have been applied:

- 019-046: Enhanced database structure
- New tables: `purchase_main`, `purchase_items`, `jci_main`, `jci_items`
- Additional columns for better functionality
- Foreign key relationships for data integrity

## Support

If you encounter any issues:

1. Check the browser console for JavaScript errors
2. Check PHP error logs
3. Verify database connection
4. Ensure all files are properly copied

## Next Steps

After successful setup:

1. **Test Login**: Verify you can log in with existing credentials
2. **Check Data**: Confirm all your data is present
3. **Test Modules**: Verify all modules work correctly
4. **Upload Files**: Check that file uploads work
5. **Backup**: Create a backup of your new `php_erp3_db` database

## Backup Recommendation

Before making any changes, create a backup:

```sql
mysqldump -u root -p php_erp3_db > php_erp3_backup.sql
```

---

**Note**: This setup preserves all your existing data while giving you access to the latest features and improvements from the updated codebase.