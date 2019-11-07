<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Slim\Http\StatusCode;
use Tests\BaseTestCase;

class ShoutingTest extends BaseTestCase
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var integer
     */
    protected $maxQuotesLimit;

    /**
     * @var Client
     */
    protected $client;

    /**
     * ShoutingTest constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->settings = (require __DIR__ . "/../../src/settings.php")['settings'];
        $this->maxQuotesLimit = $this->settings['shout']['max_limit'];

        $this->client = new Client([
            'base_uri' => 'localhost:8004',
            'headers' => ['Accept' => 'application/json'],
        ]);
    }

    public function testZigZiglar()
    {
        $this->makeSimpleTest('zig_ziglar', 20);
    }

    public function testNormanVincentPeale()
    {
        $this->makeSimpleTest('Norman_Vincent Peale', 2);
    }

    public function testAudreyHepburn()
    {
        $this->makeSimpleTest('Audrey-hepburn', 10);
    }

    public function testSteveJobs()
    {
        $this->makeSimpleTest('steve jobs', -20);
    }

    public function testSirClausMoser()
    {
        $this->makeSimpleTest('sir-claus_moser', 0);
    }

    public function testAbigailVanBuren()
    {
        $this->makeSimpleTest('--abigail van buren--');
    }

    /**
     * @param $author
     * @param null $limit
     */
    protected function makeSimpleTest($author, $limit = null)
    {
        try {
            $response = $this->client->get("shout/$author?limit=$limit");
        } catch (ClientException $e) {
            $this->assertContains("Parameter limit must not be more than {$this->maxQuotesLimit}.", $e->getMessage());

            return;
        }

        $quotes = json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);

        $this->assertEquals(StatusCode::HTTP_OK, $response->getStatusCode());
        $this->assertGreaterThan(0, count($quotes));
        $this->assertLessThanOrEqual($this->maxQuotesLimit, count($quotes));

        foreach ($quotes as $quote) {
            $this->assertEquals(mb_strtoupper($quote), $quote);
            $this->assertEquals('!', substr($quote, -1));
            $this->assertNotEquals('!', substr($quote, -2));
            $this->assertNotEquals('.', substr($quote, -2));
        }
    }
}