<?php

class SapBudgetClient
{
    private array $config;
    private static array $cache = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array{sap_status:string,message:string,data:array}
     */
    public function fetchRemainingBudget(array $params): array
    {
        if (empty($this->config['user']) || empty($this->config['pass'])) {
            throw new RuntimeException('SAP credentials are not configured');
        }

        $cacheKey = md5(json_encode($params));
        $ttl = (int) ($this->config['cache_ttl'] ?? 0);

        if ($ttl > 0 && isset(self::$cache[$cacheKey])) {
            $cached = self::$cache[$cacheKey];
            if ($cached['expires_at'] >= time()) {
                $payload = $cached['payload'];
                if (!empty($this->config['debug'])) {
                    $payload['_debug'] = array_merge($payload['_debug'] ?? [], [
                        'from_cache' => true,
                    ]);
                }
                return $payload;
            }
            unset(self::$cache[$cacheKey]);
        }

        $filteredParams = array_filter($params, static function ($value) {
            return $value !== null && $value !== '';
        });

        $url = rtrim((string) $this->config['url'], '?');
        $httpMethod = strtoupper((string) ($this->config['http_method'] ?? 'POST'));
        $headers = ['Accept: application/json'];

        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->config['user'] . ':' . $this->config['pass'],
            CURLOPT_TIMEOUT => (int) $this->config['timeout'],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => (bool) ($this->config['verify_ssl'] ?? false),
            CURLOPT_SSL_VERIFYHOST => ($this->config['verify_ssl'] ?? false) ? 2 : 0,
        ];

        $requestBody = null;

        if ($httpMethod === 'GET') {
            $url .= '?' . http_build_query($filteredParams);
            $curlOptions[CURLOPT_HTTPGET] = true;
        } else {
            $requestBody = json_encode($filteredParams, JSON_UNESCAPED_UNICODE);
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = $requestBody;
            $headers[] = 'Content-Type: application/json';
        }

        $curlOptions[CURLOPT_HTTPHEADER] = $headers;

        $ch = curl_init($url);
        curl_setopt_array($ch, $curlOptions);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException('SAP connection failed: ' . $curlError);
        }

        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            if ($httpCode >= 400) {
                throw new RuntimeException('SAP HTTP error: ' . $httpCode);
            }

            throw new RuntimeException('Invalid SAP response format');
        }

        $payload = $this->normalizeResponse($decoded);

        if (!empty($this->config['debug'])) {
            $payload['_debug'] = [
                'sap_url' => $url,
                'sap_method' => $httpMethod,
                'sap_params' => $params,
                'sap_request_body' => $requestBody,
                'http_code' => $httpCode,
                'raw_response' => $decoded,
                'from_cache' => false,
            ];
        }

        if ($ttl > 0) {
            self::$cache[$cacheKey] = [
                'expires_at' => time() + $ttl,
                'payload' => $payload,
            ];
        }

        return $payload;
    }

    /**
     * @return array{sap_status:string,message:string,data:array}
     */
    private function normalizeResponse(array $decoded): array
    {
        $status = strtoupper((string) ($decoded['STATUS'] ?? $decoded['status'] ?? 'E'));
        $message = (string) ($decoded['MESSAGE'] ?? $decoded['message'] ?? '');
        $data = $decoded['DATA'] ?? $decoded['Data'] ?? $decoded['data'] ?? [];

        if (!is_array($data)) {
            $data = [];
        }

        if ($this->isAssoc($data)) {
            $data = [$data];
        }

        $normalizedRows = [];
        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }
            $normalizedRows[] = $this->normalizeRow($row);
        }

        return [
            'sap_status' => $status,
            'message' => $message,
            'data' => $normalizedRows,
        ];
    }

    private function normalizeRow(array $row): array
    {
        $map = [
            'gjahr' => ['GJAHR', 'gjahr'],
            'balance' => ['BALANCE', 'balance', 'avail_bdg_amt', 'AVAIL_BDG_AMT'],
            'budget' => ['BUDGET', 'budget'],
            'actual' => ['ACTUAL', 'actual'],
            'pr_amt' => ['PR_AMT', 'pr_amt'],
            'po_amt' => ['PO_AMT', 'po_amt'],
            'rfund' => ['RFUND', 'rfund'],
            'fund_name' => ['FUND_NAME', 'fund_name'],
            'rfundsctr' => ['RFUNDSCTR', 'rfundsctr'],
            'fund_center_name' => ['FUND_CENTER_NAME', 'fund_center_name'],
            'rcmmtitem' => ['RCMMTITEM', 'rcmmtitem'],
            'rcmmt_text' => ['RCMMT_TEXT', 'rcmmt_text'],
            'rfuncarea' => ['RFUNCAREA', 'rfuncarea'],
            'fund_area_name' => ['FUND_AREA_NAME', 'fund_area_name'],
        ];

        $normalized = [];
        foreach ($map as $target => $candidates) {
            foreach ($candidates as $key) {
                if (array_key_exists($key, $row)) {
                    $normalized[$target] = $row[$key];
                    break;
                }
            }
        }

        return $normalized;
    }

    private function isAssoc(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
