<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ParkerClayImportController extends Controller
{
    private $baseUrl = 'https://www.parkerclay.com';
    private $brandName = 'Parker Clay';

    public function import(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            return response()->json(['message' => 'User must be associated with a tenant'], 403);
        }

        try {
            // Fetch brand logo
            $logoUrl = $this->fetchBrandLogo();

            // Get products from website (using sample data for now)
            $products = $this->getProductsFromWebsite();

            $imported = [];
            $errors = [];

            foreach ($products as $productData) {
                try {
                    $sku = $this->generateSku($productData['name'], $productData['color'] ?? 'Default');

                    // Check if exists
                    $existing = Product::where('tenant_id', $tenantId)
                        ->where('sku', $sku)
                        ->first();

                    if ($existing) {
                        $errors[] = "Product {$productData['name']} already exists";
                        continue;
                    }

                    // Download image
                    $imageUrl = null;
                    if (!empty($productData['image_url'])) {
                        $imageUrl = $this->downloadImage($productData['image_url'], 'products');
                    }

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

                    $imported[] = $product;
                } catch (\Exception $e) {
                    $errors[] = "Error importing {$productData['name']}: " . $e->getMessage();
                }
            }

            return response()->json([
                'message' => 'Import completed',
                'imported' => count($imported),
                'errors' => $errors,
                'products' => $imported,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function fetchBrandLogo()
    {
        $logoPaths = ['/logo.png', '/images/logo.png', '/assets/logo.png', '/logo.svg'];
        
        foreach ($logoPaths as $path) {
            try {
                $url = $this->baseUrl . $path;
                $response = Http::timeout(10)->head($url);
                if ($response->successful()) {
                    return $this->downloadImage($url, 'brands');
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return null;
    }

    private function getProductsFromWebsite()
    {
        // Product data extracted from Parker Clay website
        return [
            ['name' => 'Layla Satchel', 'price' => 188.00, 'color' => 'Brown', 'description' => 'Elegant satchel bag'],
            ['name' => 'Ojai Shoulder Bag', 'price' => 288.00, 'color' => 'Brown', 'description' => 'Stylish shoulder bag'],
            ['name' => 'Eden Carryall Tote Bag', 'price' => 248.00, 'color' => 'Brown', 'description' => 'Spacious carryall tote'],
            ['name' => 'Abby Drawstring Backpack', 'price' => 248.00, 'color' => 'Brown', 'description' => 'Chic drawstring backpack'],
            ['name' => 'Nyala Foldover Clutch', 'price' => 178.00, 'color' => 'Brown', 'description' => 'Elegant foldover clutch'],
            ['name' => 'Acacia Woven Handbag', 'price' => 248.00, 'color' => 'Brown', 'description' => 'Beautiful woven handbag'],
            ['name' => 'Bale Sling Bag', 'price' => 228.00, 'color' => 'Brown', 'description' => 'Best-selling sling bag'],
            ['name' => 'Mari Backpack', 'price' => 398.00, 'color' => 'Brown', 'description' => 'Premium leather backpack'],
            ['name' => 'Taytu Weekender Bag', 'price' => 448.00, 'color' => 'Brown', 'description' => 'Best-selling weekender'],
            ['name' => 'Montecito Weekender', 'price' => 698.00, 'color' => 'Brown', 'description' => 'Premium weekender bag'],
            ['name' => 'Miramar Leather Backpack', 'price' => 248.00, 'color' => 'Brown', 'description' => 'Stylish leather backpack'],
            ['name' => 'Tigi Card Wallet', 'price' => 42.00, 'color' => 'Brown', 'description' => 'Compact card wallet'],
            ['name' => 'Napa Handbag', 'price' => 278.00, 'color' => 'Brown', 'description' => 'Elegant handbag'],
            ['name' => 'Bizu Travel Pouch', 'price' => 98.00, 'color' => 'Brown', 'description' => 'Travel pouch'],
            ['name' => 'Addis Leather Passport Wallet', 'price' => 68.00, 'color' => 'Brown', 'description' => 'Leather passport wallet'],
            ['name' => 'Desta Belt Bag', 'price' => 218.00, 'color' => 'Brown', 'description' => 'Stylish belt bag'],
            ['name' => 'Abel Compact Bucket Shoulder Bag', 'price' => 158.00, 'color' => 'Brown', 'description' => 'Compact bucket bag'],
        ];
    }

    private function generateSku($productName, $color)
    {
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
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension)) {
                $contentType = $response->header('Content-Type');
                if (strpos($contentType, 'image/png') !== false) {
                    $extension = 'png';
                } elseif (strpos($contentType, 'image/jpeg') !== false) {
                    $extension = 'jpg';
                } elseif (strpos($contentType, 'image/svg') !== false) {
                    $extension = 'svg';
                } else {
                    $extension = 'jpg';
                }
            }

            $filename = $folder . '/' . Str::random(40) . '.' . $extension;
            Storage::disk('public')->put($filename, $response->body());
            
            return 'storage/' . $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}

