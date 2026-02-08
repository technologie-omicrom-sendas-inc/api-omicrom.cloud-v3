
<?php

if (!function_exists('pp')) {
    function pp($mixed = null)
    {
        echo '<pre>';
        var_dump($mixed);
        echo '</pre>';

        return null;
    }
}

/// Sequential processing
if (!function_exists('getOmicromNews')) {
    /**
     * Fetches JSON data from the Omicrom Data API
     * 
     * @param string $category News category (e.g., 'technology')
     * @param string $country Country code (e.g., 'canada')
     * @param string $date Date in YYYY-MM-DD format
     * @param string $api_key Your API key
     * @return array|null Decoded JSON data or null on failure
     */
    function getOmicromNews($category, $country, $date, $api_key) {
        // Validate inputs
        if (empty($category) || empty($country) || empty($date) || empty($api_key)) {
            error_log("Omicrom API Error: Missing required parameters");
            return null;
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
            error_log("Omicrom API Error: Invalid date format - must be YYYY-MM-DD");
            return null;
        }
        
        // Build URL
        $url = sprintf(
            'https://www.omicrom-data.com/v3.php?category=%s&country=%s&date=%s&api_key=%s',
            urlencode($category),
            urlencode($country),
            urlencode($date),
            urlencode($api_key)
        );
        echo "======================================================== \n";
        echo "GET - URL:// - {$category} - {$country} | ".$date."\n";
        echo "======================================================== \n";
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 360,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Omicrom.cloud/1.0 (+https://omicrom.cloud)',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Connection: close'
            ]
        ]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            error_log("Omicrom API cURL Error: $error");
            return null;
        }
        
        // Handle HTTP errors
        if ($httpCode !== 200) {
            error_log("Omicrom API HTTP Error: $httpCode for URL: $url");
            return null;
        }
        
        // Decode JSON
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Omicrom API JSON Error: " . json_last_error_msg());
            return null;
        }
        
        return $data;
    }
}


// Usage example:
/*
$apiKey = '5J5M59R8ZI80-3370L83J873T-838343050CC1-4KS8931P96Y7-0352782A8348';
$news = getOmicromNews('technology', 'canada', '2026-01-02', $apiKey);

if ($news !== null) {
    // Process news data
    print_r($news);
} else {
    // Handle error
    echo "Failed to fetch news data";
}
*/


if (!function_exists('fetchOmicromData')) {
    /**
     * Extract JSON data from Omicrom.Cloud API
     * 
     * @param string $apiKey Your API key
     * @param array $params Query parameters (category, country, date, etc.)
     * @param array $options Configuration options
     * @return array Associative array with response data or error information
     */
    function fetchOmicromData(
        string $apiKey,
        array $params = [],
        array $options = []
    ): array {
        // Default parameters
        $defaultParams = [
            'category' => 'technology',
            'country' => 'canada',
            'date' => date('Y-m-d'),
            'api_key' => $apiKey
        ];
        
        // Default options
        $defaultOptions = [
            'timeout' => 30,
            'max_retries' => 3,
            'retry_delay' => 1000, // milliseconds
            'verify_ssl' => true,
            'user_agent' => 'Omicrom-API-Client/1.0',
            'log_errors' => true,
            'error_log_path' => __DIR__ . '/omicrom_api_errors.log'
        ];
        
        // Merge with provided parameters and options
        $queryParams = array_merge($defaultParams, $params);
        $config = array_merge($defaultOptions, $options);
        
        // Build URL
        $url = 'https://www.omicrom-data.com/v3.php?' . http_build_query($queryParams);
        
        // Initialize cURL with production-ready settings
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $config['timeout'],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => $config['verify_ssl'],
            CURLOPT_SSL_VERIFYHOST => $config['verify_ssl'] ? 2 : 0,
            CURLOPT_USERAGENT => $config['user_agent'],
            CURLOPT_ENCODING => '', // Enable all supported encodings
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Cache-Control: no-cache',
                'Content-Type: application/json'
            ]
        ]);
        
        // Retry logic
        $attempt = 0;
        $lastError = null;
        
        while ($attempt < $config['max_retries']) {
            $attempt++;
            
            if ($attempt > 1) {
                // Log retry attempt
                if ($config['log_errors']) {
                    error_log(
                        sprintf(
                            '[%s] Retry attempt %d for URL: %s',
                            date('Y-m-d H:i:s'),
                            $attempt,
                            str_replace($apiKey, '***REDACTED***', $url)
                        ),
                        3,
                        $config['error_log_path']
                    );
                }
                
                // Exponential backoff with jitter
                $delay = $config['retry_delay'] * pow(2, $attempt - 1);
                $jitter = rand(0, 200); // Add jitter to prevent thundering herd
                usleep(($delay + $jitter) * 1000);
            }
            
            // Execute request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($error) {
                $lastError = $error;
                
                // Log cURL error
                if ($config['log_errors']) {
                    error_log(
                        sprintf(
                            '[%s] cURL Error (Attempt %d): %s - URL: %s',
                            date('Y-m-d H:i:s'),
                            $attempt,
                            $error,
                            str_replace($apiKey, '***REDACTED***', $url)
                        ),
                        3,
                        $config['error_log_path']
                    );
                }
                
                continue; // Try again
            }
            
            // Check HTTP status code
            if ($httpCode >= 400) {
                $lastError = "HTTP Error $httpCode";
                
                // Log HTTP error
                if ($config['log_errors']) {
                    error_log(
                        sprintf(
                            '[%s] HTTP Error %d (Attempt %d): %s - URL: %s',
                            date('Y-m-d H:i:s'),
                            $httpCode,
                            $attempt,
                            $response ? substr($response, 0, 200) : 'No response',
                            str_replace($apiKey, '***REDACTED***', $url)
                        ),
                        3,
                        $config['error_log_path']
                    );
                }
                
                if ($httpCode >= 500) {
                    continue; // Retry on server errors (5xx)
                }
                break; // Don't retry on client errors (4xx)
            }
            
            // Parse JSON response
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $lastError = 'Invalid JSON response: ' . json_last_error_msg();
                
                // Log JSON parse error
                if ($config['log_errors']) {
                    error_log(
                        sprintf(
                            '[%s] JSON Parse Error (Attempt %d): %s - Response: %s',
                            date('Y-m-d H:i:s'),
                            $attempt,
                            json_last_error_msg(),
                            substr($response, 0, 500)
                        ),
                        3,
                        $config['error_log_path']
                    );
                }
                
                continue; // Try again
            }
            
            // Success - clean up and return
            curl_close($ch);
            
            return [
                'success' => true,
                'data' => $data,
                'http_code' => $httpCode,
                'attempts' => $attempt,
                'timestamp' => date('c')
            ];
        }
        
        // All retries failed
        curl_close($ch);
        
        return [
            'success' => false,
            'error' => $lastError ?? 'Maximum retry attempts exceeded',
            'http_code' => $httpCode ?? 0,
            'attempts' => $attempt,
            'timestamp' => date('c'),
            'url' => str_replace($apiKey, '***REDACTED***', $url)
        ];
    }
}

if (!function_exists('fetchTechnologyDataFromCanada')) {
    /**
     * Convenience function with specific parameters from your example
     * 
     * @param string $apiKey Your API key
     * @param string $category Data category
     * @param string $country Country code
     * @param string $date Date in Y-m-d format
     * @param array $options Additional options
     * @return array Response data
     */
    function fetchTechnologyDataFromCanada(
        string $apiKey,
        string $date = '2026-01-02',
        array $options = []
    ): array {
        return fetchOmicromData($apiKey, [
            'category' => 'technology',
            'country' => 'canada',
            'date' => $date
        ], $options);
    }
}

if (!function_exists('validateApiDate')) {
    /**
     * Validate and format date for API
     * 
     * @param string $date Date string
     * @return string|null Formatted date or null if invalid
     */
    function validateApiDate(string $date): ?string
    {
        try {
            $dateTime = new DateTime($date);
            return $dateTime->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('get_multiple_categories_and_countries')) {
    function get_multiple_categories_and_countries($list_category, $array_countries, $date, $api_key, $redis_local) {
        foreach ($list_category as $category_name) {
            $category_clean = clean_underscorString($category_name);
            
            foreach ($array_countries as $country_ISO => $country_name) {
                $country_clean = clean_underscorString_advanced($country_name);

                $array_param = [
                    'category' => $category_clean,
                    'country' => $country_clean,
                    'date' => $date
                ];
                var_dump($array_param);

                $articles = getOmicromNews($category_clean, $country_clean, $date, $api_key);
                $array_articles_result = $articles['articles'];
                
                if (isset($array_articles_result) && !empty($array_articles_result)) {
                    foreach ($array_articles_result as $key => $list_articles) {
                        if (isset($list_articles) && is_array($list_articles)) {
                            $article_ID = $list_articles['article_ID'];
                            $article_title = $list_articles['article_title'];
                            $date_string = $list_articles['date_string'];
                            $article_text_ID = $list_articles['article_text_ID'];
                            $dict_article = $list_articles['dict_article'];

                            /// Store brut GET - request
                            $group_articles = [
                                'article_ID' => $article_ID,
                                'article_title' => $article_title,
                                'db_date_string' => $date_string,
                                'article_text_ID' => $article_text_ID,
                                'dict_article'=>json_encode($dict_article),

                                'category' => $category_clean,
                                'country' => $country_clean,
                                'date' => $date,
                                'datetime' => date("Y-m-d H:i:s"),
                            ];

                            $store_key = "articles-bruts:{$date}:{$category_clean}:{$country_clean}:{$article_ID}";
                            $structure_result = insertRecord_inRedis_HMSET($redis_local, $store_key, $group_articles);
                            var_dump($structure_result);
                        }
                    }

                    

                    sleep(5);
                    echo "====================================";
                }else {

                }
            }
            sleep(5);
        }
    }
}


// ============================================================================
// USAGE EXAMPLES
// ============================================================================

/*
// Example 1: Basic usage with your specific URL parameters
$apiKey = '5J5M59R8ZI80-3370L83J873T-838343050CC1-4KS8931P96Y7-0352782A8348';
$result = fetchTechnologyDataFromCanada($apiKey, '2026-01-02');

if ($result['success']) {
    $data = $result['data'];
    // Process your data
    echo "Success! Retrieved " . count($data) . " records.\n";
} else {
    echo "Error: " . $result['error'] . "\n";
}

// Example 2: Custom parameters
$result = fetchOmicromData($apiKey, [
    'category' => 'finance',
    'country' => 'us',
    'date' => '2026-01-01'
], [
    'timeout' => 60,
    'max_retries' => 5
]);

// Example 3: With error logging disabled
$result = fetchOmicromData($apiKey, [
    'category' => 'technology',
    'country' => 'canada',
    'date' => '2026-01-02'
], [
    'log_errors' => false
]);

// Example 4: Date validation
$date = validateApiDate('2026-01-02');
if ($date) {
    $result = fetchTechnologyDataFromCanada($apiKey, $date);
}
*/

?>