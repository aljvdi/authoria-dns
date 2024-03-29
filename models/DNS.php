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
     * @param int|null $ttl The TTL of verification token in second, if null, it will be set to 300 (5 minutes).
     * @return string The verification UUID.
     */
    public function newRequest(string $domain, ?int $ttl): string
    {
        $this->_db->getDBInstance()->insert('dns', [
            'domain' => $domain,
            'ttl' => $ttl ?? 300,
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
            if (time() - $record['created_at'] > $record['ttl']) {
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
                'status' => $record['verified'] ? 'VERIFIED' : (time() - $record['created_at'] > $record['ttl'] ? 'EXPIRED' : 'PENDING'),
            ];
        }, $this->_db->getDBInstance()->select('dns', '*', ['uid' => $uuid]))[0] ?? [
            'id' => $uuid,
            'domain' => "unkown.tld",
            'verified' => false,
            'status' => 'NOT_FOUND'
        ];
    }

    /**
     * Check if a domain is valid. Basically, it checks if the domain exists (has NS records), is not a reserved domain, has a valid TLD, and is in a valid format.
     * @param string $domain The domain to check. (e.g. example.com)
     * @return bool True if the domain is valid, false if not.
     */
    public static function domainValidator(string $domain): bool
    {
        $domain_exists = checkdnsrr($domain, 'NS');
        $invalid_or_reserved_domains = ['localhost'];
        $invalid_tlds = ['test', 'example', 'invalid', 'localhost','onion','home','local'];
        $domains_valid_regex_format = '/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/';

        return $domain_exists // Domain exists
            && !in_array($domain, $invalid_or_reserved_domains) // Domain is not invalid or reserved
            && !in_array(explode('.', $domain)[1], $invalid_tlds) // TLD is not invalid
            && preg_match($domains_valid_regex_format, $domain); // Domain is in a valid format

    }
}