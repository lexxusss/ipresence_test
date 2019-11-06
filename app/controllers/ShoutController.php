<?php

namespace App\Controllers;

use App\service\ShoutService;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Slim\Http\Request;
use Slim\Http\Response;


/**
 * Class ShoutController
 * @package App\Controllers
 */
class ShoutController
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var ShoutService
     */
    protected $shoutService;

    /**
     * ShoutController constructor.
     * @param ShoutService $shoutService
     */
    public function __construct(ShoutService $shoutService)
    {
        $this->settings = (require __DIR__ . "/../../src/settings.php")['settings'];

        $this->shoutService = $shoutService;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return int
     * @throws PhpfastcacheInvalidArgumentException
     */
    public function shout(Request $request, Response $response, $args = [])
    {
        $personOriginal = $request->getAttribute('person');
        $personParsed = strtolower(trim(snakeCaseToSpaceCase($personOriginal)));

        $maxLimit = $this->settings['shout']['max_limit'];
        $limit = min(abs($request->getQueryParam('limit', $maxLimit)), $maxLimit);

        $found = $this->shoutService->getShoutQuotes($personParsed, $limit);

        return $response
            ->getBody()
            ->write(
                json_encode($found, JSON_UNESCAPED_UNICODE)
            );
    }
}