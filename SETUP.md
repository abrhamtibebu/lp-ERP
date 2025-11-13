# Setup Guide - Parker Clay ERP System

This guide will help you set up and run the Parker Clay ERP system with separate frontend and backend folders.

## Project Structure

```
lp-ERP/
├── backend/          # Laravel API (root directory)
│   ├── app/
│   ├── routes/
│   ├── database/
│   └── ...
├── frontend/         # Vue 3 SPA
│   ├── src/
│   ├── package.json
│   └── vite.config.js
└── ...
```

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL or compatible database

## Backend Setup (Laravel)

1. **Navigate to backend folder:**
   ```bash
   cd backend
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Copy environment file:**
   ```bash
   copy .env.example .env
   # or on Linux/Mac: cp .env.example .env
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Configure database in `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=leather_erp
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations and seeders:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Create storage link:**
   ```bash
   php artisan storage:link
   ```

8. **Publish Sanctum config (if needed):**
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

## Frontend Setup (Vue 3)

1. **Navigate to frontend folder:**
   ```bash
   cd frontend
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

3. **Create `.env` file (optional):**
   ```env
   VITE_API_URL=http://localhost:8000/api
   ```

## Running the Application

### Option 1: Run Both Services (Recommended)

**Windows:**
```bash
# Double-click start-all.bat
# OR run in PowerShell:
.\start-all.ps1
```

**Linux/Mac:**
```bash
# Terminal 1 - Backend
cd backend
php artisan serve

# Terminal 2 - Frontend
cd frontend
npm run dev
```

### Option 2: Run Separately

**Backend only:**
```bash
cd backend
php artisan serve
# Backend runs on http://localhost:8000
```

**Frontend only:**
```bash
cd frontend
npm run dev
# Frontend runs on http://localhost:3000
```

## Accessing the Application

- **Frontend:** http://localhost:3000
- **Backend API:** http://localhost:8000/api
- **API Health Check:** http://localhost:8000/up

## Default Users

After running seeders, you'll need to create a user. You can use Tinker:

```bash
cd backend
php artisan tinker
```

Then create a tenant and user:
```php
$tenant = App\Models\Tenant::create([
    'name' => 'Demo Company',
    'slug' => 'demo',
    'leather_consumption_mode' => 'formula'
]);

$user = App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'department' => 'HR',
    'position' => 'Manager',
    'employed_on' => now(),
    'emergency_contact' => 'Contact Info'
]);

$gmRole = App\Models\Role::where('name', 'GM')->first();
$user->roles()->attach($gmRole->id, ['tenant_id' => $tenant->id]);
```

## Troubleshooting

### CORS Issues
If you encounter CORS errors, ensure:
1. `config/cors.php` has the correct frontend URL
2. Backend is running on port 8000
3. Frontend is running on port 3000

### Database Connection Issues
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check database exists

### Frontend Build Issues
- Delete `node_modules` and `package-lock.json`, then run `npm install` again
- Ensure Node.js version is 18+

### Sanctum Issues
- Run `php artisan config:clear`
- Ensure `SANCTUM_STATEFUL_DOMAINS` in `.env` includes `localhost:3000`

## Development

### Backend Development
- API routes: `routes/api.php`
- Controllers: `app/Http/Controllers/API/`
- Models: `app/Models/`
- Migrations: `database/migrations/`

### Frontend Development
- Components: `frontend/src/views/`
- Stores: `frontend/src/stores/`
- Router: `frontend/src/router/`
- API Client: `frontend/src/api/client.js`

## Production Build

### Backend
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend
```bash
cd frontend
npm run build
```

Built files will be in `frontend/dist/`

## Support

For issues or questions, refer to the main README.md file.

