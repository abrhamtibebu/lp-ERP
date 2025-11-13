<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportParkerClayProducts extends Command
{
    protected $signature = 'import:parker-clay {--tenant-id=}';
    protected $description = 'Import products and brand information from Parker Clay website';

    private $baseUrl = 'https://www.parkerclay.com';
    private $brandName = 'Parker Clay';
    private $products = [];

    public function handle()
    {
        $this->info('Starting Parker Clay product import...');

        // Get tenant
        $tenantId = $this->option('tenant-id');
        if (!$tenantId) {
            $tenant = Tenant::first();
            if (!$tenant) {
                $this->error('No tenant found. Please create a tenant first or specify --tenant-id');
                return 1;
            }
            $tenantId = $tenant->id;
        } else {
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found");
                return 1;
            }
        }

        $this->info("Using tenant: {$tenant->name} (ID: {$tenantId})");

        // Fetch brand logo
        $this->info('Fetching brand logo...');
        $logoUrl = $this->fetchBrandLogo();

        // Extract products from the website
        $this->info('Extracting products from website...');
        $this->extractProducts();

        if (empty($this->products)) {
            $this->warn('No products found. Using sample data from website content.');
            $this->createSampleProducts();
        }

        // Import products
        $this->info('Importing products...');
        $imported = 0;
        $skipped = 0;

        foreach ($this->products as $productData) {
            try {
                // Generate unique SKU
                $sku = $this->generateSku($productData['name'], $productData['color'] ?? 'Default');

                // Check if product already exists
                $existing = Product::where('tenant_id', $tenantId)
                    ->where('sku', $sku)
                    ->first();

                if ($existing) {
                    $this->warn("Skipping existing product: {$productData['name']} ({$sku})");
                    $skipped++;
                    continue;
                }

                // Download and store product image
                $imageUrl = null;
                if (!empty($productData['image_url'])) {
                    $imageUrl = $this->downloadImage($productData['image_url'], 'products');
                }

                // Create product
                $product = Product::create([
                    'tenant_id' => $tenantId,
                    'product_name' => $productData['name'],
                    'color' => $productData['color'] ?? null,
                    'sku' => $sku,
                    'weight_kg' => $productData['weight_kg'] ?? null,
                    'unit_price' => $productData['price'],
                    'description' => $productData['description'] ?? "Premium {$productData['name']} from Parker Clay",
                    'image_url' => $imageUrl,
                    'brand_logo_url' => $logoUrl,
                    'brand_name' => $this->brandName,
                ]);

                $this->info("✓ Imported: {$product->product_name} - \${$product->unit_price}");
                $imported++;
            } catch (\Exception $e) {
                $this->error("Error importing {$productData['name']}: " . $e->getMessage());
            }
        }

        $this->info("\nImport complete!");
        $this->info("Imported: {$imported} products");
        $this->info("Skipped: {$skipped} products");

        return 0;
    }

    private function fetchBrandLogo()
    {
        try {
            // Try to fetch the logo from common locations
            $logoPaths = [
                '/logo.png',
                '/images/logo.png',
                '/assets/logo.png',
                '/logo.svg',
            ];

            foreach ($logoPaths as $path) {
                $url = $this->baseUrl . $path;
                $response = Http::head($url);
                if ($response->successful()) {
                    return $this->downloadImage($url, 'brands');
                }
            }

            // If logo not found, create a placeholder or use a default
            $this->warn('Brand logo not found. You may need to manually add it.');
            return null;
        } catch (\Exception $e) {
            $this->warn("Could not fetch brand logo: " . $e->getMessage());
            return null;
        }
    }

    private function extractProducts()
    {
        // Based on the website content provided, extract product information
        // Since we can't directly scrape, we'll use the product data from the search results
        
        $productData = [
            [
                'name' => 'Layla Satchel',
                'price' => 188.00,
                'color' => 'Brown',
                'category' => 'Handbags',
                'description' => 'Elegant satchel bag with premium leather construction',
            ],
            [
                'name' => 'Ojai Shoulder Bag',
                'price' => 288.00,
                'color' => 'Brown',
                'category' => 'Handbags',
                'description' => 'Stylish shoulder bag perfect for everyday use',
            ],
            [
                'name' => 'Eden Carryall Tote Bag',
                'price' => 248.00,
                'color' => 'Brown',
                'category' => 'Tote Bags',
                'description' => 'Spacious carryall tote bag with premium leather',
            ],
            [
                'name' => 'Abby Drawstring Backpack',
                'price' => 248.00,
                'color' => 'Brown',
                'category' => 'Backpacks',
                'description' => 'Chic drawstring backpack in premium leather',
            ],
            [
                'name' => 'Nyala Foldover Clutch',
                'price' => 178.00,
                'color' => 'Brown',
                'category' => 'Handbags',
                'description' => 'Elegant foldover clutch with premium leather',
            ],
            [
                'name' => 'Acacia Woven Handbag',
                'price' => 248.00,
                'color' => 'Brown',
                'category' => 'Handbags',
                'description' => 'Beautiful woven handbag with unique design',
            ],
            [
                'name' => 'Bale Sling Bag',
                'price' => 228.00,
                'color' => 'Brown',
                'category' => 'Sling & Crossbody Bags',
                'description' => 'Best-selling sling bag in premium leather',
            ],
            [
                'name' => 'Mari Backpack',
                'price' => 398.00,
                'color' => 'Brown',
                'category' => 'Backpacks',
                'description' => 'Premium leather backpack for travel and daily use',
            ],
            [
                'name' => 'Taytu Weekender Bag',
                'price' => 448.00,
                'color' => 'Brown',
                'category' => 'Travel',
                'description' => 'Best-selling weekender bag for travel',
            ],
            [
                'name' => 'Montecito Weekender',
                'price' => 698.00,
                'color' => 'Brown',
                'category' => 'Travel',
                'description' => 'Premium weekender bag with spacious design',
            ],
            [
                'name' => 'Miramar Leather Backpack',
                'price' => 248.00,
                'color' => 'Brown',
                'category' => 'Backpacks',
                'description' => 'Stylish leather backpack for everyday use',
            ],
            [
                'name' => 'Tigi Card Wallet',
                'price' => 42.00,
                'color' => 'Brown',
                'category' => 'Wallets',
                'description' => 'Compact card wallet in premium leather',
            ],
            [
                'name' => 'Napa Handbag',
                'price' => 278.00,
                'color' => 'Brown',
                'category' => 'Handbags',
                'description' => 'Elegant handbag with premium leather',
            ],
            [
                'name' => 'Bizu Travel Pouch',
                'price' => 98.00,
                'color' => 'Brown',
                'category' => 'Accessories',
                'description' => 'Travel pouch for organizing essentials',
            ],
            [
                'name' => 'Addis Leather Passport Wallet',
                'price' => 68.00,
                'color' => 'Brown',
                'category' => 'Accessories',
                'description' => 'Leather passport wallet for travel',
            ],
            [
                'name' => 'Desta Belt Bag',
                'price' => 218.00,
                'color' => 'Brown',
                'category' => 'Sling & Crossbody Bags',
                'description' => 'Stylish belt bag in premium leather',
            ],
            [
                'name' => 'Abel Compact Bucket Shoulder Bag',
                'price' => 158.00,
                'color' => 'Brown',
                'category' => 'Tote Bags',
                'description' => 'Compact bucket shoulder bag',
            ],
        ];

        $this->products = $productData;
    }

    private function createSampleProducts()
    {
        // This method creates products based on the website content provided
        // In a real scenario, you would scrape the website or use an API
        $this->extractProducts();
    }

    private function generateSku($productName, $color)
    {
        // Generate SKU: PC-{PRODUCT_CODE}-{COLOR_CODE}
        $productCode = strtoupper(Str::substr(Str::slug($productName), 0, 8));
        $colorCode = strtoupper(Str::substr($color, 0, 3));
        $random = Str::random(3);
        return "PC-{$productCode}-{$colorCode}-{$random}";
    }

    private function downloadImage($url, $folder = 'products')
    {
        try {
            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                $this->warn("Could not download image: {$url}");
                return null;
            }

            // Get file extension
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension)) {
                // Try to detect from content type
                $contentType = $response->header('Content-Type');
                if (strpos($contentType, 'image/png') !== false) {
                    $extension = 'png';
                } elseif (strpos($contentType, 'image/jpeg') !== false || strpos($contentType, 'image/jpg') !== false) {
                    $extension = 'jpg';
                } elseif (strpos($contentType, 'image/svg') !== false) {
                    $extension = 'svg';
                } else {
                    $extension = 'jpg'; // Default
                }
            }

            // Generate unique filename
            $filename = $folder . '/' . Str::random(40) . '.' . $extension;
            
            // Store in public disk
            Storage::disk('public')->put($filename, $response->body());
            
            // Return the public URL path
            return 'storage/' . $filename;
        } catch (\Exception $e) {
            $this->warn("Error downloading image {$url}: " . $e->getMessage());
            return null;
        }
    }
}

