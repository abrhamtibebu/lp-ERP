# Parker Clay Product Import Guide

## Overview
This system allows you to import products and brand information from Parker Clay website (https://www.parkerclay.com/) into your ERP system.

## Features
- ✅ Import product catalog from Parker Clay
- ✅ Download and store product images
- ✅ Download and store brand logo
- ✅ Automatic SKU generation
- ✅ Duplicate detection (skips existing products)
- ✅ Image storage in public storage

## Setup

### 1. Run Database Migration
First, add the image fields to the products table:

```bash
cd backend
php artisan migrate
```

This will add the following fields to the `products` table:
- `image_url` - URL/path to product image
- `brand_logo_url` - URL/path to brand logo
- `brand_name` - Brand name (e.g., "Parker Clay")

### 2. Ensure Storage Link
Make sure the storage link is created:

```bash
php artisan storage:link
```

## Usage

### Method 1: Via Web Interface (Recommended)

1. Navigate to **Products** page in the ERP system
2. Click the **"Import Parker Clay"** button (visible only if you have `products.manage` permission)
3. Confirm the import in the dialog
4. Wait for the import to complete
5. View imported products in the products grid

### Method 2: Via Artisan Command

```bash
cd backend
php artisan import:parker-clay
```

Or specify a tenant:

```bash
php artisan import:parker-clay --tenant-id=1
```

## Imported Products

The system imports the following products from Parker Clay:

1. Layla Satchel - $188.00
2. Ojai Shoulder Bag - $288.00
3. Eden Carryall Tote Bag - $248.00
4. Abby Drawstring Backpack - $248.00
5. Nyala Foldover Clutch - $178.00
6. Acacia Woven Handbag - $248.00
7. Bale Sling Bag - $228.00
8. Mari Backpack - $398.00
9. Taytu Weekender Bag - $448.00
10. Montecito Weekender - $698.00
11. Miramar Leather Backpack - $248.00
12. Tigi Card Wallet - $42.00
13. Napa Handbag - $278.00
14. Bizu Travel Pouch - $98.00
15. Addis Leather Passport Wallet - $68.00
16. Desta Belt Bag - $218.00
17. Abel Compact Bucket Shoulder Bag - $158.00

## Product Data Structure

Each imported product includes:
- **Product Name**: Full product name
- **Price**: Unit price in USD
- **Color**: Product color (defaults to "Brown")
- **SKU**: Auto-generated unique SKU (format: `PC-{PRODUCT_CODE}-{COLOR_CODE}-{RANDOM}`)
- **Description**: Product description
- **Image URL**: Path to downloaded product image
- **Brand Logo URL**: Path to Parker Clay brand logo
- **Brand Name**: "Parker Clay"

## API Endpoint

You can also import via API:

```bash
POST /api/products/import/parker-clay
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Import completed",
  "imported": 17,
  "errors": [],
  "products": [...]
}
```

## Image Storage

- Product images are stored in: `storage/app/public/products/`
- Brand logos are stored in: `storage/app/public/brands/`
- Images are accessible via: `storage/{filename}`

## Notes

- Products with duplicate SKUs are automatically skipped
- Images are downloaded and stored locally
- If image download fails, product is still created without image
- Brand logo is fetched from common logo locations on the website
- All products are associated with your tenant

## Troubleshooting

### Images not displaying?
1. Ensure `php artisan storage:link` has been run
2. Check that `storage/app/public` directory exists and is writable
3. Verify image URLs in the database

### Import fails?
1. Check your internet connection
2. Verify you have `products.manage` permission
3. Check backend logs for detailed error messages
4. Ensure tenant_id is set for your user

### Duplicate products?
- The system automatically skips products with existing SKUs
- To re-import, delete existing products first or modify SKU generation

## Future Enhancements

Potential improvements:
- Real-time web scraping from Parker Clay website
- Support for multiple product colors/variants
- Bulk image download optimization
- Product category mapping
- Price synchronization
- Inventory level updates

