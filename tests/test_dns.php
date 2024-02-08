<?php
use PHPUnit\Framework\TestCase;
use Javadi\Authoria\DNS\models\DNS;

class test_dns extends TestCase
{
    private $dns;

    protected function setUp(): void
    {
        $this->dns = new DNS();
    }

    public function testNewRequest()
    {
        $uuid = $this->dns->newRequest('example.com', 300000);
        $this->assertIsString($uuid);
    }

    public function testGetRequestStatus()
    {
        $uuid = $this->dns->newRequest('example.com', 300000);
        $status = $this->dns->getRequestStatus($uuid);
        $this->assertIsArray($status);
        $this->assertArrayHasKey('id', $status);
        $this->assertArrayHasKey('domain', $status);
        $this->assertArrayHasKey('verified', $status);
        $this->assertArrayHasKey('status', $status);
    }

    public function testVerifyRequest()
    {
        $uuid = $this->dns->newRequest('example.com', 300000);
        $verified = $this->dns->verifyRequest($uuid);
        $this->assertIsBool($verified);
    }

    public function testDomainValidator()
    {
        $this->assertTrue(DNS::domainValidator('example.com'));
        $this->assertFalse(DNS::domainValidator('example.com.'));
        $this->assertFalse(DNS::domainValidator('127.0.0.1')); // IP address is not a domain, BTW.
        $this->assertFalse(DNS::domainValidator('example'));
    }
}
