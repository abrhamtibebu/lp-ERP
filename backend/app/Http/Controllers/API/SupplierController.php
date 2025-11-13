<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name', 'asc')
            ->get();
        
        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'business_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'woreda' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'products_supplied' => 'nullable|string',
            'contact_info' => 'nullable|string',
        ]);

        $supplier = Supplier::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'tin_number' => $request->tin_number,
            'business_number' => $request->business_number,
            'address' => $request->address,
            'woreda' => $request->woreda,
            'house_number' => $request->house_number,
            'phone_number' => $request->phone_number,
            'products_supplied' => $request->products_supplied,
            'contact_info' => $request->contact_info,
        ]);

        return response()->json($supplier->fresh(), 201);
    }

    public function show($id)
    {
        $supplier = Supplier::where('tenant_id', auth()->user()->tenant_id)
            ->with(['leatherInventories' => function($query) {
                $query->orderBy('purchase_date', 'desc');
            }])
            ->findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'business_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'woreda' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'products_supplied' => 'nullable|string',
            'contact_info' => 'nullable|string',
        ]);

        $supplier->update($request->only([
            'name', 'tin_number', 'business_number', 'address', 'woreda', 
            'house_number', 'phone_number', 'products_supplied', 'contact_info'
        ]));

        return response()->json($supplier->fresh());
    }

    public function destroy($id)
    {
        $supplier = Supplier::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }

    /**
     * Fetch business information from Ethiopian Trade Bureau API by TIN number
     * 
     * This method calls the external Ethiopian Trade Bureau API using cURL to retrieve
     * business registration information based on the provided TIN number.
     * The data is then mapped to our supplier form fields.
     * 
     * @param string $tinNumber The TIN (Tax Identification Number) to lookup
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBusinessInfoByTin($tinNumber)
    {
        try {
            // Validate TIN number format (basic validation - adjust as needed)
            if (empty($tinNumber) || !preg_match('/^[0-9]+$/', $tinNumber)) {
                return response()->json([
                    'error' => 'Invalid TIN number format',
                    'message' => 'Please provide a valid TIN number'
                ], 400);
            }

            // Call the Ethiopian Trade Bureau API using cURL
            // API endpoint: https://etrade.gov.et/api/Registration/GetRegistrationInfoByTin/{tinNumber}/am
            $apiResult = $this->getByTin($tinNumber);
            
            Log::info("Fetching business info from Ethiopian Trade Bureau API", [
                'tin_number' => $tinNumber,
                'api_result_status' => $apiResult['status'] ?? 'ERROR'
            ]);

            // Check if the API call was successful
            if (!isset($apiResult['status']) || $apiResult['status'] !== 'OK') {
                $errorMessage = $apiResult['error'] ?? 'Unknown error occurred';
                // Get HTTP code from API response - convert to int for proper comparison
                $apiHttpCode = isset($apiResult['http_code']) ? (int)$apiResult['http_code'] : null;
                $retryAfter = $apiResult['retry_after'] ?? null; // Get retry_after if available
                
                Log::warning("Ethiopian Trade Bureau API returned error", [
                    'tin_number' => $tinNumber,
                    'error' => $errorMessage,
                    'api_http_code' => $apiHttpCode,
                    'api_http_code_type' => gettype($apiHttpCode),
                    'retry_after' => $retryAfter,
                    'api_result_keys' => array_keys($apiResult)
                ]);

                // Map HTTP status codes from API to appropriate response codes for frontend
                // Pass through specific status codes (429, 403, 404) so frontend can handle them appropriately
                $responseCode = 404; // Default to 404 for not found
                
                // Check for specific status codes (use strict comparison with int)
                if ($apiHttpCode === 429 || $apiHttpCode == 429) {
                    // Rate limiting - return 429 so frontend can handle it
                    $responseCode = 429;
                    Log::info("Mapping 429 rate limit error to frontend", ['tin_number' => $tinNumber]);
                } else if ($apiHttpCode === 403 || $apiHttpCode == 403) {
                    // Forbidden - return 403
                    $responseCode = 403;
                } else if ($apiHttpCode === 404 || $apiHttpCode == 404) {
                    // Not found - return 404
                    $responseCode = 404;
                } else if ($apiHttpCode !== null && $apiHttpCode >= 500) {
                    // Server errors from external API -> return 502 (Bad Gateway)
                    $responseCode = 502;
                } else if ($apiHttpCode !== null && $apiHttpCode >= 400) {
                    // Other client errors -> return 400 (Bad Request)
                    $responseCode = 400;
                }
                
                Log::info("Returning error response to frontend", [
                    'tin_number' => $tinNumber,
                    'api_http_code' => $apiHttpCode,
                    'response_code' => $responseCode,
                    'error_message' => $errorMessage
                ]);
                
                // Return error response with appropriate status code
                return response()->json([
                    'error' => 'API request failed',
                    'message' => $errorMessage ?? 'Unable to fetch business information. Please check the TIN number and try again.',
                    'http_code' => $apiHttpCode, // Include original API HTTP code for reference
                    'retry_after' => $retryAfter // Include retry_after if available (for 429 errors)
                ], $responseCode);
            }

            // Get the result data
            $data = $apiResult['result'] ?? null;

            // Check if we received valid data
            if (empty($data) || (!is_array($data) && !is_object($data))) {
                Log::info("No business information found for TIN", ['tin_number' => $tinNumber]);
                
                return response()->json([
                    'error' => 'No data found',
                    'message' => 'No business information found for the provided TIN number.'
                ], 404);
            }

            // Convert object to array if needed
            if (is_object($data)) {
                $data = json_decode(json_encode($data), true);
            }

            // Log the raw API response structure for debugging
            Log::info("Raw API response structure", [
                'tin_number' => $tinNumber,
                'available_keys' => is_array($data) ? array_keys($data) : 'Not an array',
                'data_preview' => is_array($data) ? json_encode(array_slice($data, 0, 10, true)) : $data
            ]);

            // Helper function to get value from array with case-insensitive key lookup
            // This helps handle variations in field name casing
            $getValue = function($array, $keys) {
                if (!is_array($array)) {
                    return '';
                }
                // Try exact match first (case-sensitive)
                foreach ($keys as $key) {
                    if (isset($array[$key])) {
                        return $array[$key];
                    }
                }
                // Try case-insensitive match
                $arrayLower = array_change_key_case($array, CASE_LOWER);
                foreach ($keys as $key) {
                    $keyLower = strtolower($key);
                    if (isset($arrayLower[$keyLower])) {
                        return $arrayLower[$keyLower];
                    }
                }
                return '';
            };

            // Map API response fields to our supplier form fields
            // Priority: BusinessName (exact case) > businessName > Name > name, etc.
            // Note: The API returns fields like BusinessName, RegNo, etc.
            $mappedData = [
                // Name field: Map from BusinessName (primary), then fallback to other variations
                'name' => $getValue($data, ['BusinessName', 'businessName', 'Name', 'name', 'companyName', 'business_name', 'CompanyName']),
                // Business Number: Map from RegNo (primary), then fallback to other variations
                'business_number' => $getValue($data, ['RegNo', 'regNo', 'REGNO', 'RegistrationNumber', 'registrationNumber', 'businessNumber', 'registration_number', 'business_number']),
                // Address: Check various possible field names
                'address' => $getValue($data, ['Address', 'address', 'businessAddress', 'BusinessAddress', 'registeredAddress', 'business_address']),
                // Woreda: Check various possible field names
                'woreda' => $getValue($data, ['Woreda', 'woreda', 'SubCity', 'subCity', 'woredaName', 'sub_city']),
                // House Number: Check various possible field names
                'house_number' => $getValue($data, ['HouseNumber', 'houseNumber', 'house_number', 'BuildingNumber', 'buildingNumber', 'building_number']),
                // Phone Number: Check various possible field names
                'phone_number' => $getValue($data, ['PhoneNumber', 'phoneNumber', 'Phone', 'phone', 'Telephone', 'telephone', 'contactNumber', 'phone_number']),
                // Contact Info: Check various possible field names
                'contact_info' => $getValue($data, ['OwnerName', 'ownerName', 'Owner', 'owner', 'ContactPerson', 'contactPerson', 'ContactName', 'contactName', 'owner_name', 'contact_person']),
                // Legal Status: Check various possible field names
                'legal_status' => $getValue($data, ['LegalStatus', 'legalStatus', 'Status', 'status', 'RegistrationStatus', 'registrationStatus', 'legal_status']),
            ];

            // Log what was actually mapped for debugging
            Log::info("Field mapping results", [
                'tin_number' => $tinNumber,
                'mapped_name' => $mappedData['name'],
                'mapped_business_number' => $mappedData['business_number'],
                'mapped_address' => $mappedData['address'],
                'all_mapped_fields' => array_keys(array_filter($mappedData, function($value) {
                    return $value !== '' && $value !== null;
                }))
            ]);

            // Remove empty values to avoid overwriting existing data with empty strings
            $mappedData = array_filter($mappedData, function($value) {
                return $value !== '' && $value !== null;
            });

            Log::info("Successfully fetched business info from Ethiopian Trade Bureau API", [
                'tin_number' => $tinNumber,
                'fields_found' => array_keys($mappedData)
            ]);

            // Return the mapped data along with the raw response for debugging (optional)
            return response()->json([
                'success' => true,
                'data' => $mappedData,
                'raw_data' => config('app.debug') ? $data : null // Only include raw data in debug mode
            ]);

        } catch (\Exception $e) {
            // Handle any errors (connection errors, timeouts, etc.)
            $errorMessage = $e->getMessage();
            
            Log::error("Unexpected error when fetching business info", [
                'tin_number' => $tinNumber,
                'error' => $errorMessage,
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal error',
                'message' => 'An error occurred while fetching business information. Please try again.'
            ], 500);
        }
    }

    /**
     * Call Ethiopian Trade Bureau API using cURL
     * 
     * This method uses cURL to make the API request with proper headers and SSL settings.
     * It handles redirects and returns the decoded JSON response.
     * 
     * @param string $tinNumber The TIN (Tax Identification Number) to lookup
     * @return array Returns ['status' => 'OK', 'result' => ...] on success or ['status' => 'ERROR', 'error' => ...] on failure
     */
    private function getByTin($tinNumber)
    {
        $ch = curl_init();

        $url = "https://etrade.gov.et/api/Registration/GetRegistrationInfoByTin/" . $tinNumber . "/am";

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true, // Include headers in response
            CURLOPT_FOLLOWLOCATION => true, // Follow redirects (302, 301, etc.)
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification (may be needed for some servers)
            CURLOPT_SSL_VERIFYHOST => 0,     // Disable host verification
            CURLOPT_TIMEOUT => 30,           // Set timeout to 30 seconds
            CURLOPT_CONNECTTIMEOUT => 10,    // Set connection timeout to 10 seconds
            CURLOPT_HTTPHEADER => [
                'Referer: https://etrade.gov.et', // Set Referer header (required by API)
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        // Extract Retry-After header if present (for rate limiting)
        $retryAfter = null;
        if ($response !== false) {
            // Get response headers to check for Retry-After header (for rate limiting)
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            
            // Extract Retry-After header if present (for 429 responses)
            if (preg_match('/Retry-After:\s*(\d+)/i', $headers, $matches)) {
                $retryAfter = (int)$matches[1];
            }
            
            // Use body as response (since we separated headers)
            $response = $body;
        }

        curl_close($ch);

        // Check for cURL errors
        if ($curlErrno) {
            Log::error("cURL error when calling Ethiopian Trade Bureau API", [
                'tin_number' => $tinNumber,
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno,
                'http_code' => $httpCode
            ]);

            return [
                'status' => 'ERROR',
                'error' => 'Connection error: ' . $curlError
            ];
        }

        // Check HTTP status code
        if ($httpCode >= 400) {
            Log::warning("HTTP error when calling Ethiopian Trade Bureau API", [
                'tin_number' => $tinNumber,
                'http_code' => $httpCode,
                'response_preview' => substr($response, 0, 200)
            ]);

            // Handle specific HTTP status codes
            if ($httpCode === 429) {
                // Rate limiting - Too Many Requests
                $retryMessage = 'Rate limit exceeded. Please wait a few moments before trying again.';
                if ($retryAfter !== null) {
                    $retryMessage = "Rate limit exceeded. Please wait {$retryAfter} seconds before trying again.";
                }
                
                Log::warning("Rate limit exceeded when calling Ethiopian Trade Bureau API", [
                    'tin_number' => $tinNumber,
                    'http_code' => 429,
                    'retry_after' => $retryAfter
                ]);
                
                return [
                    'status' => 'ERROR',
                    'error' => $retryMessage,
                    'http_code' => 429,
                    'retry_after' => $retryAfter
                ];
            } else if ($httpCode === 404) {
                // Not found
                return [
                    'status' => 'ERROR',
                    'error' => 'No business information found for this TIN number.',
                    'http_code' => 404
                ];
            } else if ($httpCode === 403) {
                // Forbidden - might be authentication or permission issue
                return [
                    'status' => 'ERROR',
                    'error' => 'Access denied. The API may require authentication or the request is not allowed.',
                    'http_code' => 403
                ];
            } else if ($httpCode >= 500) {
                // Server errors
                return [
                    'status' => 'ERROR',
                    'error' => 'The business registration service is temporarily unavailable. Please try again later.',
                    'http_code' => $httpCode
                ];
            } else {
                // Other client errors (400-499)
                return [
                    'status' => 'ERROR',
                    'error' => 'API returned HTTP status ' . $httpCode . '. Please check the TIN number and try again.',
                    'http_code' => $httpCode
                ];
            }
        }

        // Decode JSON response (preserve case of keys)
        $decodedResponse = json_decode($response, true);

        // Log raw response for debugging (first 500 chars)
        Log::debug("Raw API response (first 500 chars)", [
            'tin_number' => $tinNumber,
            'response_preview' => substr($response, 0, 500),
            'response_length' => strlen($response)
        ]);

        // Check if JSON decoding was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("JSON decode error when calling Ethiopian Trade Bureau API", [
                'tin_number' => $tinNumber,
                'json_error' => json_last_error_msg(),
                'response_preview' => substr($response, 0, 200)
            ]);

            // Check if response is HTML (likely a redirect to login page)
            if (strpos($response, '<html') !== false || strpos($response, '<!DOCTYPE') !== false) {
                return [
                    'status' => 'ERROR',
                    'error' => 'API redirected to login page. The endpoint may require authentication.'
                ];
            }

            return [
                'status' => 'ERROR',
                'error' => 'Invalid JSON response from API'
            ];
        }

        // Return successful response
        return [
            'status' => 'OK',
            'result' => $decodedResponse
        ];
    }
}

