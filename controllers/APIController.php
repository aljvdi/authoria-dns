<?php

namespace Javadi\Authoria\DNS\controllers;
use Javadi\Authoria\DNS\models\DNS;

class APIController
{
    private DNS $_dns;

    public function __construct()
    {
        $this->_dns = new DNS();
    }

    public function init(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
        header('X-Powered-By: Javadi-Authoria-DNS/0.0.1');
    }

    /**
     * Check if the API is the one we are looking for.
     * @return void
     */
    public function isThatAuthoria(): void
    {
        echo json_encode([
            'authoria' => true,
            'time' => time()
        ]);
    }

    /**
     * Check if the API is alive and if the server has internet access.
     * @return void
     */
    public function aliveCheck(): void
    {
        echo json_encode([
            'api-alive' => true,
            'internet' => !empty(dns_get_record('google.com', DNS_NS))
        ]);
    }

    public function notFound(): void
    {
        echo json_encode(['error' => 'Endpoint not found.']);
    }

    /**
     * Create a new DNS verification request.
     * @EndPoint /api/v1/new
     * @Method POST
     * @return void
     */
    public function newRequest(): void
    {
        $domain = $_POST['domain'];
        $ttl = $_POST['ttl'] ?? 300;

        if (!empty($domain)) {
            $domain_exists = checkdnsrr($domain, 'NS');
            if ($domain_exists) {
                $uuid = $this->_dns->newRequest($domain, $ttl);
                echo json_encode([
                    'id' => $uuid,
                    'domain' => $domain,
                    'TXT_record_to_verify' => "authoria-dns-verification=" . hash('sha256', $uuid),
                    'expires_at' => time() + $ttl
                ]);
            } else {
                echo json_encode(['error' => 'Domain does not exist.']);
            }
        } else {
            echo json_encode(['error' => 'Domain is required.']);
        }
    }

    /**
     * Verify a DNS verification request.
     * @EndPoint /api/v1/verify
     * @Method GET
     * @return void
     */
    public function verifyRequest(): void
    {
        $id = $_GET['id'];

        if (!empty($id)) {
            $verified = $this->_dns->verifyRequest($id);
            echo json_encode($this->_dns->getRequestStatus($id));
        } else {
            echo json_encode(['error' => 'ID is required.']);
        }
    }

    /**
     * Bulk verify DNS verification requests.
     * @EndPoint /api/v1/bulk-verify
     * @Method POST
     * @return void
     */
    public function bulkVerifyRequest(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        if (!empty($body['ids'])) {
            $results = [];

            foreach ($body['ids'] as $id) {
                $this->_dns->verifyRequest($id);
                $results[] = $this->_dns->getRequestStatus($id);
            }

            echo json_encode($results);
        } else {
            echo json_encode(['error' => 'IDs are required.']);
        }
    }

}