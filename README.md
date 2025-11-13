# Parker Clay ERP System

A comprehensive multi-tenant SaaS ERP platform for leather goods manufacturing, built with Laravel 12 and Vue 3.

## Project Structure

The project is organized with separate frontend and backend folders:

```
lp-ERP/
├── backend/                 # Laravel API (separate folder)
│   ├── app/                 # Application code
│   ├── routes/              # API routes
│   ├── database/            # Migrations and seeders
│   ├── config/              # Configuration files
│   └── ...
├── frontend/                # Vue 3 SPA (separate folder)
│   ├── src/                 # Vue components and stores
│   ├── package.json         # Frontend dependencies
│   └── vite.config.js       # Vite configuration
└── start scripts            # Convenience scripts to run both
```

## Quick Start

See [SETUP.md](SETUP.md) for detailed setup instructions.

**Quick Start:**
1. Backend: `cd backend && composer install && php artisan migrate && php artisan db:seed`
2. Frontend: `cd frontend && npm install`
3. Run: Use `start-all.bat` (Windows) or run both services separately

## Features

- **Multi-Tenant Architecture**: Single database with tenant isolation
- **Role-Based Access Control**: GM, HR, Inventory Manager, Production Supervisor, Logistics, Finance
- **Employee Management**: Complete employee lifecycle with document management
- **Inventory Management**: Leather and accessories inventory with consumption tracking
- **Production Workflow**: Batch-based production with stage-by-stage tracking
- **Finance Module**: Product costing, expenses, revenue tracking
- **Logistics**: Commercial invoice generation with PDF support
- **Reporting**: WIP tracking, inventory levels, consumption variance, cost breakdown

## Technology Stack

- **Backend**: Laravel 12.x
- **Frontend**: Vue 3 (Composition API) + Tailwind CSS
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **State Management**: Pinia
- **Routing**: Vue Router

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd lp-ERP
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Update `.env` file** with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Create storage link**
   ```bash
   php artisan storage:link
   ```

8. **Build frontend assets**
   ```bash
   npm run build
   # Or for development:
   npm run dev
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

## Project Structure

### Backend (Laravel)

- `app/Models/` - Eloquent models with multi-tenant support
- `app/Http/Controllers/API/` - RESTful API controllers
- `app/Services/` - Business logic services (BatchService, ProductionService, ConsumptionService)
- `app/Http/Middleware/` - Tenant and permission middleware
- `database/migrations/` - Database schema migrations
- `database/seeders/` - Database seeders for roles, permissions, production stages

### Frontend (Vue 3)

- `resources/js/app.js` - Main Vue application entry point
- `resources/js/router/` - Vue Router configuration
- `resources/js/stores/` - Pinia stores (auth, tenant)
- `resources/js/views/` - Page components
- `resources/js/layouts/` - Layout components
- `resources/js/api/` - API client configuration

## Key Modules

### 1. Employee Management
- Create, edit employees with department assignment
- Document upload support
- Role assignment by GM

### 2. Inventory Management
- Leather inventory with consumption tracking
- Accessories inventory
- Support for formula-based, manual, or hybrid consumption modes per tenant

### 3. Production Workflow
- Order creation
- Batch generation with auto-generated batch IDs
- Stage-by-stage movement tracking
- WIP inventory management
- Automatic finished goods movement

### 4. Finance Module
- Product cost locking (Finance role only)
- Expense registration with cost centers
- Advance allocation
- Revenue tracking from invoices

### 5. Logistics
- Commercial invoice generation
- PDF template support
- File attachment management
- Auto-fill from order/batch data

## Multi-Tenant Architecture

The system uses a single database with `tenant_id` on all relevant tables. The `TenantModel` base class automatically applies tenant scoping:

- Global scope filters queries by `tenant_id`
- Auto-injection of `tenant_id` on create
- Middleware ensures tenant context from authenticated user

## Role-Based Access Control

### Roles
- **GM**: Full system access
- **HR**: Employee and asset management
- **Inventory Manager**: Leather and accessories inventory
- **Production Supervisor**: Orders and production stage updates
- **Logistics**: Commercial invoice creation
- **Finance**: Product costs, expenses, revenue

### Permissions
Permissions are granular and can be assigned to roles. GM role automatically has all permissions.

## API Endpoints

All API endpoints are prefixed with `/api` and require authentication via Laravel Sanctum.

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get authenticated user

### Core Modules
- `GET|POST /api/employees` - Employee management
- `GET|POST /api/suppliers` - Supplier management
- `GET|POST /api/fixed-assets` - Fixed assets
- `GET|POST /api/leather-inventory` - Leather inventory
- `GET|POST /api/accessories-inventory` - Accessories inventory
- `GET|POST /api/products` - Product/SKU management

### Production
- `GET|POST /api/orders` - Order management
- `POST /api/orders/{order}/create-batch` - Create batch from order
- `GET|POST /api/batches` - Batch management
- `POST /api/batches/{batch}/move-stage` - Move batch to stage
- `GET /api/batches/{batch}/wip-status` - Get WIP status

### Finance
- `GET|POST /api/product-costs` - Product cost management
- `GET|POST /api/expenses` - Expense registration
- `GET|POST /api/advances` - Advance allocation
- `GET|POST /api/revenues` - Revenue tracking

### Logistics
- `GET|POST /api/commercial-invoices` - Commercial invoice management
- `GET /api/commercial-invoices/{invoice}/pdf` - Generate PDF

### Reports
- `GET /api/reports/wip-tracker` - WIP stage tracker
- `GET /api/reports/inventory-levels` - Inventory stock levels
- `GET /api/reports/finished-goods-aging` - Finished goods aging

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Frontend Development
```bash
npm run dev
```

## Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Build frontend assets: `npm run build`

## File Storage

The system uses local filesystem storage by default. Files are stored in `storage/app/public/`:
- Employee documents: `storage/app/public/employee_documents/`
- Invoice attachments: `storage/app/public/invoice_attachments/`

To migrate to AWS S3, update `config/filesystems.php` and set `FILESYSTEM_DISK=s3` in `.env`.

## License

This project is proprietary software.

## Support

For issues or questions, please contact the development team.
