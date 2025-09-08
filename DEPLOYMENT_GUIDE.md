# 🚀 PHP ERP3 - Complete Deployment Guide

## 📋 Pre-Deployment Checklist

### System Requirements
- ✅ PHP 7.4 or higher
- ✅ MySQL 5.7 or higher
- ✅ Apache/Nginx web server
- ✅ Required PHP extensions: PDO, PDO_MySQL, mbstring, openssl, json

### Files to Upload
```
php_erp3/
├── config/
├── modules/
├── uploads/
├── migrations/
├── include/
├── assets/
├── superadmin/
├── salesadmin/
├── accountsadmin/
├── core/
├── libs/
├── vendor/
├── .htaccess
├── index.php
├── setup_complete.php
├── php_erp3_db.sql
└── README.md
```

## 🔧 Deployment Steps

### Step 1: Upload Files
1. Upload all project files to your web server
2. Ensure proper file permissions (755 for directories, 644 for files)
3. Make uploads directory writable (755 or 777)

### Step 2: Database Setup
1. Create a new MySQL database
2. Import the database using one of these methods:

**Option A: Using setup_complete.php (Recommended)**
```
https://yourdomain.com/setup_complete.php
Password: purewood_setup_2025
```

**Option B: Manual Import**
```sql
mysql -u username -p database_name < php_erp3_db.sql
```

### Step 3: Configuration
Update `config/config.php` with your database credentials:

```php
// Live/Production configuration
'host' => 'localhost',
'db' => 'your_database_name',
'user' => 'your_db_username',
'pass' => 'your_db_password',
'base_url' => 'https://yourdomain.com/',
```

### Step 4: Environment Setup
Create `.env` file in project root:
```
APP_ENV=production
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_db_username
DB_PASS=your_db_password
BASE_URL=https://yourdomain.com/
```

### Step 5: Directory Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/invoice/
chmod 755 uploads/builty/
chmod 755 uploads/po/
chmod 755 uploads/quotation/
```

## 🔐 Default Login Credentials

**Super Admin**
- Email: `superadmin@purewood.in`
- Password: `Admin@123`

**Accounts Admin** (if exists)
- Email: `accounts@purewood.in`
- Password: `Admin@123`

⚠️ **Change these passwords immediately after first login!**

## 📊 System Features

### Core Modules
- ✅ **Lead Management** - Track and manage customer leads
- ✅ **Quotation System** - Create and manage quotations with products
- ✅ **Purchase Orders** - Generate and track purchase orders
- ✅ **BOM Management** - Bill of Materials with cost calculations
- ✅ **Job Card Instructions** - Manufacturing job cards
- ✅ **Purchase Management** - Supplier purchase tracking
- ✅ **Payment System** - Payment tracking and approval
- ✅ **Image Upload** - Support for invoice and builty images
- ✅ **Approval Workflows** - Multi-level approval system
- ✅ **Multi-user Access** - Role-based access control

### User Roles
- **Super Admin** - Full system access
- **Sales Admin** - Lead and quotation management
- **Accounts Admin** - Purchase and payment management
- **Operation Admin** - BOM and JCI management
- **Production Admin** - Manufacturing operations

## 🔍 Post-Deployment Testing

### Test Checklist
1. ✅ Login with default credentials
2. ✅ Create a new lead
3. ✅ Generate a quotation
4. ✅ Create a purchase order
5. ✅ Upload images (invoice/builty)
6. ✅ Test approval workflows
7. ✅ Verify payment system
8. ✅ Check all modules load correctly

### Common Issues & Solutions

**Database Connection Error**
- Check database credentials in config.php
- Ensure database server is running
- Verify database name exists

**File Upload Issues**
- Check uploads directory permissions
- Verify PHP upload_max_filesize setting
- Ensure web server has write permissions

**Module Not Loading**
- Check file paths in includes
- Verify all required files are uploaded
- Check PHP error logs

## 🔄 Updates & Maintenance

### Regular Maintenance
- Backup database regularly
- Monitor uploads directory size
- Update PHP and MySQL versions
- Review user access permissions

### Updating System
1. Backup current files and database
2. Upload new files
3. Run any new migrations
4. Test all functionality

## 📞 Support

For technical support or issues:
- Check error logs in `/logs/` directory
- Review PHP error logs
- Contact system administrator

## 🔒 Security Recommendations

1. **Change Default Passwords** - Update all default login credentials
2. **SSL Certificate** - Use HTTPS for production
3. **File Permissions** - Set appropriate file/directory permissions
4. **Database Security** - Use strong database passwords
5. **Regular Updates** - Keep PHP and MySQL updated
6. **Backup Strategy** - Implement regular automated backups

---

**Version**: PHP ERP3 v1.0  
**Last Updated**: September 2025  
**Deployment Ready**: ✅ Yes