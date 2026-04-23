<?php

namespace Nomba\Tests;

use PHPUnit\Framework\TestCase;
use Nomba\NombaClient;
use Nomba\Resources\Accounts;
use Nomba\Resources\VirtualAccounts;
use Nomba\Resources\Payments;
use Nomba\Resources\Transfers;

class NombaClientTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = new NombaClient([
            'client_id' => 'test_id',
            'client_secret' => 'test_secret',
            'account_id' => 'test_account_id',
            'environment' => 'sandbox'
        ]);
    }

    public function testClientInitialization()
    {
        $this->assertInstanceOf(NombaClient::class, $this->client);
    }

    public function testResourcesAreAccessible()
    {
        $this->assertInstanceOf(Accounts::class, $this->client->accounts);
        $this->assertInstanceOf(VirtualAccounts::class, $this->client->virtualAccounts);
        $this->assertInstanceOf(Payments::class, $this->client->payments);
        $this->assertInstanceOf(Transfers::class, $this->client->transfers);
    }
}

