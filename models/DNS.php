<?php
/**
 * DNS class, responsible for handling verification of DNS records.
 * @package Javadi\Authoria\DNS\models
 * @version 1.0.0
 * @since 1.0.0
 * @author Alex Javadi
 */
namespace Javadi\Authoria\DNS\models;
use Javadi\Authoria\DNS\models\DB;

class DNS
{
    private DB $_db;

    public function __construct()
    {
        $this->_db = new DB();
    }

    /**
     * Get the DNS records for a domain.
     * @param string $domain The domain to verify.
     * @param int|null $ttl The TTL of verification token in ms, if null, it will be set to 300000 (5 minutes).
     * @return string The verification UUID.
     */
    public function newRequest(string $domain, ?int $ttl): string
    {
        $this->_db->getDBInstance()->insert('dns', [
            'domain' => $domain,
            'ttl' => $ttl ?? 300000,
            'uid' => $uuid = DB::uuidV4(),
            'verified' => 0,
            'created_at' => time()
        ]);

        return $uuid;
    }


    /**
     * Get the DNS records for a domain.
     * @param string $domain The domain to get the DNS records for.
     * @return array The DNS TXT records.
     */
    private static function getDnsTxtRecords(string $domain): array
    {
        return array_map(function($records) {
            return $records['txt'];
        }, dns_get_record($domain, DNS_TXT));
    }

    /**
     * Verify a DNS record.
     * @param string $uuid The UUID of the verification request.
     * @return bool True if the record was verified, false if not.
     */
    public function verifyRequest(string $uuid): bool
    {
        $record = $this->_db->getDBInstance()->get('dns', '*', ['uid' => $uuid]);

        if ($record && $record['verified'] == 0) {
            // check ttl
            if (time() * 1000 - $record['created_at'] > $record['ttl']) {
                return false;
            }

            $_record_to_check = "authoria-dns-verification=". hash('sha256', $uuid);
            $dns_records = self::getDnsTxtRecords($record['domain']);

            if (in_array($_record_to_check, $dns_records)) {
                return $this->_db->getDBInstance()->update('dns', ['verified' => 1, "updated_at" => time()], ['uid' => $uuid])->execute();
            }
        }
        return false;
    }

    /**
     * Get the status of a verification request.
     * @param string $uuid The UUID of the verification request.
     * @return array The status of the request.
     */
    public function getRequestStatus(string $uuid):  array
    {
        return array_map(function($record) {
            return [
                'id' => $record['uid'],
                'domain' => $record['domain'],
                'verified' => $record['verified'] == 1,
                'status' => $record['verified'] ? 'VERIFIED' : (time() * 1000 - $record['created_at'] > $record['ttl'] ? 'EXPIRED' : 'PENDING'),
            ];
        }, $this->_db->getDBInstance()->select('dns', '*', ['uid' => $uuid]))[0];
    }
}