# CAB - Bank Reconciliation System

A full-stack web application for managing bank reconciliation tables with Excel-like interface and role-based access control.

## Features

🧾 **Excel-like Interface**
- Import bank transactions from Excel/CSV files
- Inline editing with AG-Grid
- Automatic field calculations
- Manual field completion tracking

🔐 **Role-Based Access Control**
- **Admin**: Full access, field configuration, all row editing
- **Importer**: Excel import only, no editing
- **Editor**: Manual field editing in unlocked rows only
- **Viewer**: Read-only access

🛠 **Admin Panel**
- Configure field behavior (imported/manual/calculated)
- Control which fields are editable
- Manage user roles and permissions

📥 **Import System**
- Excel/CSV file upload via Laravel Excel
- Configurable column mapping
- Only imports fields marked as 'imported'
- Automatic row creation

📤 **Export System**
- Export to Excel (.xlsx) with all data
- Export to PDF with RTL support (Arabic)
- Filtering and search capabilities

🧱 **Database Structure**
- `users`: Authentication and roles
- `transactions`: All transaction data with status tracking
- `field_settings`: Column configuration and behavior

## Tech Stack

### Backend (Laravel 11+)
- **Laravel 11.x** - PHP framework
- **Laravel Sanctum** - API authentication
- **Laravel Excel** - Excel import/export
- **DomPDF** - PDF generation
- **SQLite** - Database (configurable)

### Frontend (React 18+)
- **React 18** with TypeScript
- **Tailwind CSS** - Styling
- **AG-Grid** - Excel-like data grid
- **React Query** - State management and API calls
- **React Router** - Navigation
- **Axios** - HTTP client

## Installation

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 16+
- npm/yarn

### Backend Setup

1. Navigate to backend directory:
```bash
cd backend
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations and seed database:
```bash
php artisan migrate:fresh --seed
```

6. Start the server:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Frontend Setup

1. Navigate to frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Create environment file:
```bash
# Create .env file with:
REACT_APP_API_URL=http://localhost:8000/api
```

4. Start development server:
```bash
npm start
```

## Default Users

The system comes with pre-seeded users for testing:

| Email | Password | Role | Access |
|-------|----------|------|--------|
| admin@example.com | password | Admin | Full access |
| importer@example.com | password | Importer | Import only |
| editor@example.com | password | Editor | Edit manual fields |
| viewer@example.com | password | Viewer | Read-only |

## Usage

### 1. Login
- Visit `http://localhost:3000`
- Login with any of the default users above

### 2. Dashboard
- View statistics and recent activity
- Quick navigation to main features

### 3. Import Transactions
- Go to Import page (Admin/Importer only)
- Download template to see expected format
- Upload Excel/CSV file
- System imports only configured fields

### 4. Edit Transactions
- Go to Transactions page
- Inline editing in grid
- Blue cells = editable by Editors
- Gray cells = calculated fields
- Red cells = locked rows (Admin only can edit)

### 5. Export Data
- Go to Export page
- Choose Excel or PDF format
- Apply filters if needed
- Download generated file

### 6. Configure Fields (Admin Only)
- Go to Field Settings
- Mark fields as imported/manual/calculated
- Control which fields appear in import/edit

## Database Schema

### Users Table
```sql
- id: Primary key
- name: User full name
- email: Login email (unique)
- password: Hashed password
- role: enum(admin, importer, editor, viewer)
```

### Transactions Table
```sql
- id: Primary key
- post_date, value_date: Transaction dates
- description, doctor_name, reference: Text fields
- amount, balance, specialist: Imported amounts
- registration, yearly, exam, certificate, newsletters, other, visa: Manual amounts
- unspecified, summary, commission, total, difference: Calculated fields
- inward_number, inward_date: Additional tracking
- notes: Free text
- is_locked: Row completion status
- completed_by_user_id: Who completed the row
- completed_at: When completed
```

### Field Settings Table
```sql
- id: Primary key
- field_name: Column identifier
- field_type: enum(imported, manual, calculated)
- is_editable: Boolean flag
- display_order: Sort order
```

## Calculated Fields

The system automatically calculates these fields:

- **Unspecified** = IF(Specialist == 0, Amount, 0)
- **Summary** = SUM(all manual fields)
- **Commission** = IF(Summary ≥ Amount, Summary - Amount, 0)
- **Total** = Amount + Commission
- **Difference** = Summary - Total

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/me` - Current user info

### Transactions
- `GET /api/transactions` - List transactions (paginated)
- `POST /api/transactions` - Create transaction
- `PUT /api/transactions/{id}` - Update transaction
- `DELETE /api/transactions/{id}` - Delete transaction (Admin only)

### Import/Export
- `POST /api/import/transactions` - Import Excel file
- `GET /api/import/template` - Download template
- `GET /api/export/excel` - Export to Excel
- `GET /api/export/pdf` - Export to PDF

### Field Settings
- `GET /api/field-settings` - List field configurations
- `PUT /api/field-settings/{id}` - Update field setting (Admin only)
- `GET /api/field-config` - Get field configuration for frontend

## Row Locking Logic

- Rows automatically lock when all manual fields are completed
- Locked rows show red background
- Only Admins can edit locked rows
- Editors can only edit unlocked rows
- Viewers cannot edit any rows

## Future Enhancements

- Audit log for field changes
- File attachments per transaction
- Inline comments and notes
- Email notifications
- Advanced reporting dashboard
- Multi-branch/account support
- Real-time bank data integration

## Troubleshooting

### Backend Issues
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Reset database
php artisan migrate:fresh --seed

# Check logs
tail -f storage/logs/laravel.log
```

### Frontend Issues
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Check console for errors
# Open browser dev tools
```

### CORS Issues
- Ensure CORS configuration allows frontend domain
- Check `config/cors.php` settings
- Verify API URLs match

## License

This project is open-source software licensed under the MIT license.