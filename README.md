# 🚀 PHP ERP3 - Complete Business Management System

A comprehensive ERP (Enterprise Resource Planning) system built with PHP and MySQL, featuring complete lead-to-payment workflow management.

## ✨ Key Features

- 📊 **Lead Management** - Track customer leads with status updates
- 💰 **Quotation System** - Generate professional quotations with products
- 📋 **Purchase Orders** - Create and manage purchase orders
- 🔧 **BOM Management** - Bill of Materials with cost calculations
- 📝 **Job Card Instructions** - Manufacturing job cards and tracking
- 🛒 **Purchase Management** - Supplier purchase tracking with images
- 💳 **Payment System** - Payment tracking and approval workflows
- 🖼️ **Image Support** - Upload and manage invoice/builty images
- ✅ **Approval Workflows** - Multi-level approval system
- 👥 **Multi-user Access** - Role-based access control

## Setup Instructions

### 1. Clone Repository
```bash
git clone <repository-url>
cd php_erp
```

### 2. Quick Setup (Recommended)
**Use the automated setup script:**
```
https://yourdomain.com/setup_complete.php
Password: purewood_setup_2025
```

### 3. Manual Database Setup
1. Create a MySQL database
2. Import the complete database:
```bash
mysql -u username -p database_name < php_erp3_db.sql
```
3. Update database credentials in config/config.php

### 3. Configuration
1. Copy `config/config.example.php` to `config/config.php`
2. Update the following in `config/config.php`:
   - Database credentials (DB_HOST, DB_NAME, DB_USER, DB_PASS)
   - Base URL (BASE_URL)
   - Email settings (if using email features)

### 4. File Permissions
Create the following directories and set proper permissions:
```bash
mkdir -p assets/images/upload
mkdir -p modules/quotation/uploads
mkdir -p modules/purchase/uploads
mkdir -p modules/po/uploads
mkdir -p modules/bom/uploads
chmod 755 assets/images/upload
chmod 755 modules/*/uploads
```

### 5. Web Server Configuration
- Point your web server document root to the project directory
- Ensure PHP 7.4+ is installed
- Enable required PHP extensions: PDO, PDO_MySQL, GD, mbstring

## Features

- **Lead Management** - Create and manage business leads
- **Quotation System** - Generate quotations with approval workflow
- **Customer Management** - Track customer interactions and history
- **PI (Proforma Invoice)** - Automatic PI generation from locked quotations
- **Multi-user Access** - Different admin roles (Super, Sales, Accounts, Operations, Production)
- **Export Functionality** - PDF and Excel export for quotations and PIs
- **Email Integration** - Send quotations and PIs via email

## Admin Access

Default admin credentials will be set up during installation.

## Modules

- **Lead Module** - `/modules/lead/`
- **Quotation Module** - `/modules/quotation/`
- **Customer Module** - `/modules/customer/`
- **PI Module** - `/modules/pi/`
- **Purchase Module** - `/modules/purchase/`
- **BOM Module** - `/modules/bom/`

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server
- GD extension for image processing
- PDO extension for database connectivity

## File Structure

```
php_erp/
├── assets/           # CSS, JS, images
├── config/           # Configuration files
├── include/          # Common includes
├── modules/          # Application modules
├── superadmin/       # Super admin interface
├── salesadmin/       # Sales admin interface
├── accountsadmin/    # Accounts admin interface
└── uploads/          # File uploads (ignored by Git)
```

## License

Proprietary - Purewood Company