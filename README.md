# PHP ERP System - Purewood

A comprehensive ERP system built with PHP for managing leads, quotations, customers, and proforma invoices.

## Setup Instructions

### 1. Clone Repository
```bash
git clone <repository-url>
cd php_erp
```

### 2. Database Setup
1. Create a MySQL database
2. Import the database schema (SQL file to be provided)
3. Update database credentials in config file

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