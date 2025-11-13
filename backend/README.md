# Parker Clay ERP Backend

Laravel 12 API backend for the Parker Clay ERP System.

## Setup

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
copy .env.example .env
# or on Linux/Mac: cp .env.example .env
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Configure database in `.env`

5. Run migrations:
```bash
php artisan migrate
php artisan db:seed
```

6. Start server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## Documentation

See the main project README.md and SETUP.md for complete documentation.

