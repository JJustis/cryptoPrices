<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

class CryptoPriceService {
    private $cacheFile = 'price_cache.json';
    private $cacheDuration = 60; // Cache duration in seconds
    private $coins = ['bitcoin', 'ethereum', 'dogecoin', 'cardano', 'solana'];
    private $apiEndpoint = 'https://api.coingecko.com/api/v3/simple/price';
    
    public function getPrices() {
        try {
            // Check if we have valid cached data
            $cachedData = $this->getCachedPrices();
            if ($cachedData !== false) {
                return $this->successResponse($cachedData);
            }

            // Fetch fresh data
            $prices = $this->fetchPrices();
            if (empty($prices)) {
                throw new Exception('Failed to fetch prices');
            }

            // Cache the new data
            $this->cachePrices($prices);

            return $this->successResponse($prices);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function getCachedPrices() {
        if (!file_exists($this->cacheFile)) {
            return false;
        }

        $cacheData = json_decode(file_get_contents($this->cacheFile), true);
        if (!$cacheData || !isset($cacheData['timestamp']) || !isset($cacheData['prices'])) {
            return false;
        }

        // Check if cache is still valid
        if (time() - $cacheData['timestamp'] > $this->cacheDuration) {
            return false;
        }

        return $cacheData['prices'];
    }

    private function cachePrices($prices) {
        $cacheData = [
            'timestamp' => time(),
            'prices' => $prices
        ];
        
        file_put_contents(
            $this->cacheFile, 
            json_encode($cacheData),
            LOCK_EX
        );
    }

    private function fetchPrices() {
        $queryParams = http_build_query([
            'ids' => implode(',', $this->coins),
            'vs_currencies' => 'usd'
        ]);

        $url = $this->apiEndpoint . '?' . $queryParams;
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP CryptoWidget/1.0',
                    'Accept: application/json'
                ],
                'timeout' => 5
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Failed to fetch data from API');
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API');
        }

        $prices = [];
        foreach ($this->coins as $coin) {
            if (isset($data[$coin]['usd'])) {
                $prices[$coin] = $data[$coin]['usd'];
            }
        }

        return $prices;
    }

    private function successResponse($prices) {
        return [
            'success' => true,
            'prices' => $prices,
            'timestamp' => time()
        ];
    }

    private function errorResponse($message) {
        return [
            'success' => false,
            'error' => $message,
            'timestamp' => time()
        ];
    }
}

// Initialize and run the service
$service = new CryptoPriceService();
echo json_encode($service->getPrices());