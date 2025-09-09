# 🏢 Buyer & Supplier Module Guide - PHP ERP3

## 📋 Overview
Complete buyer and supplier management system with quotation workflows, approval processes, and dashboard analytics.

## 🔐 Access Points

### **Main Login Page**
- **URL**: `http://localhost/php_erp/`
- **Buyer Login**: Link available on main page
- **Supplier Login**: Link available on main page

### **Direct Access URLs**
- **Buyer Login**: `http://localhost/php_erp/buyeradmin/login.php`
- **Supplier Login**: `http://localhost/php_erp/supplieradmin/login.php`
- **Buyer Registration**: `http://localhost/php_erp/buyer_register.php`
- **Supplier Registration**: `http://localhost/php_erp/supplieradmin/register.php`

## 👥 User Types & Features

### **🛒 BUYER MODULE**

#### **Dashboard Features:**
- Total Quotations Count
- Active Orders Tracking
- Pending Quotations Status
- Total Earnings Overview
- Quick Actions Panel
- Recent Activity Feed

#### **Quotation Management:**
- ✅ Add New Quotations
- ✅ View All Quotations
- ✅ Upload Excel Templates
- ✅ Download Templates
- ✅ Track Approval Status

#### **Key Files:**
```
buyeradmin/
├── dashboard.php          # Main dashboard
├── login.php             # Login page
├── logout.php            # Logout handler
├── sidebar.php           # Navigation menu
└── quotation/
    ├── add.php           # Add quotation
    ├── list.php          # View quotations
    ├── save.php          # Save quotation
    ├── view.php          # View details
    └── delete.php        # Delete quotation
```

### **🏭 SUPPLIER MODULE**

#### **Dashboard Features:**
- Total Quotations Submitted
- Active Orders from Purewood
- Pending Quotations Status
- Total Earnings Tracking
- Quick Actions Panel
- Recent Activity Monitor

#### **Quotation Management:**
- ✅ Submit Quotations to Purewood
- ✅ View Quotation History
- ✅ Excel Upload Support
- ✅ Template Downloads
- ✅ Status Tracking

#### **Key Files:**
```
supplieradmin/
├── dashboard.php         # Main dashboard
├── login.php            # Login page
├── register.php         # Registration
├── profile.php          # Profile management
├── sidebar.php          # Navigation menu
└── quotation/
    ├── add.php          # Add quotation
    ├── list.php         # View quotations
    ├── save.php         # Save quotation
    └── view.php         # View details
```

## 🔄 Workflow Process

### **Buyer Workflow:**
1. **Registration** → Admin Approval → **Login**
2. **Create Quotation** → Upload Excel/Manual Entry
3. **Submit to Purewood** → Wait for Response
4. **Receive PI** → Place Order
5. **Track Order Status** → Payment Processing

### **Supplier Workflow:**
1. **Registration** → Email Verification → **Login**
2. **Receive RFQ** → Prepare Quotation
3. **Submit Quotation** → Wait for Approval
4. **Receive PO** → Fulfill Order
5. **Submit Invoice** → Receive Payment

## 🛠 Admin Management

### **Super Admin Controls:**
- **Buyer Management**: `superadmin/buyer/`
  - Approve/Reject Buyers
  - View Buyer Details
  - Manage Buyer Status

- **Supplier Management**: `superadmin/supplier/`
  - Approve/Reject Suppliers
  - View Supplier Details
  - Manage Supplier Status

### **Communication Admin:**
- **Buyer Quotations**: `communicationadmin/buyer/`
- **Supplier Quotations**: `communicationadmin/supplier/`

## 📊 Database Tables

### **Buyer Tables:**
- `buyers` - Buyer information
- `buyer_otps` - OTP verification
- `buyer_quotations` - Buyer quotations

### **Supplier Tables:**
- `suppliers` - Supplier information
- `supplier_quotations` - Supplier quotations

## 🚀 Quick Setup

### **1. Database Setup:**
```sql
-- Tables are created via migrations
-- Run: php run_migrations.php
```

### **2. Test Accounts:**
```
# Create test buyer
INSERT INTO buyers (company_name, contact_person_name, contact_person_email, password, status) 
VALUES ('Test Company', 'Test Buyer', 'buyer@test.com', '$2y$10$...', 'approved');

# Create test supplier  
INSERT INTO suppliers (company_name, contact_person_name, contact_person_email, password, status, email_verified) 
VALUES ('Test Supplier Ltd', 'Test Supplier', 'supplier@test.com', '$2y$10$...', 'active', 1);
```

### **3. File Permissions:**
```bash
chmod 755 buyeradmin/quotation/uploads/
chmod 755 supplieradmin/quotation/uploads/
```

## 🔧 Configuration

### **Email Settings:**
- Configure SMTP in `config/config.php`
- Required for supplier email verification
- Required for password reset functionality

### **Upload Settings:**
- Excel file uploads supported
- Image uploads for quotations
- File size limits configurable

## 📱 Features Summary

### **✅ Implemented Features:**
- User registration & login
- Dashboard analytics
- Quotation management
- Excel upload/download
- Email verification
- Password reset
- Admin approval workflows
- Status tracking

### **🔄 Integration Points:**
- Links with main ERP quotation system
- Connects to PI generation
- Integrates with purchase orders
- Payment tracking integration

## 🎯 Usage Instructions

### **For Buyers:**
1. Register at `/buyer_register.php`
2. Wait for admin approval
3. Login at `/buyeradmin/login.php`
4. Create quotations via dashboard
5. Track order status

### **For Suppliers:**
1. Register at `/supplieradmin/register.php`
2. Verify email address
3. Login at `/supplieradmin/login.php`
4. Submit quotations to Purewood
5. Manage orders and invoices

### **For Admins:**
1. Manage buyers in `superadmin/buyer/`
2. Manage suppliers in `superadmin/supplier/`
3. Review quotations in `communicationadmin/`
4. Process approvals and rejections

## 🔗 Related Modules
- **Quotations**: Main quotation system
- **PI**: Proforma Invoice generation
- **Purchase**: Purchase order management
- **Payments**: Payment processing

---

**🎉 Your Buyer & Supplier modules are ready to use!**