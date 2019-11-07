<?php

namespace App\Controllers;

use App\Exceptions\MaxLimitAchievedException;
use App\Requests\ShoutRequest;
use App\service\ShoutService;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;


/**
 * Class ShoutController
 * @package App\Controllers
 */
class ShoutController
{
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
        $this->shoutService = $shoutService;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return int|Response
     * @throws PhpfastcacheInvalidArgumentException
     */
    public function shout(Request $request, Response $response, $args = [])
    {
        $shoutRequest = new ShoutRequest($request);

        try {
            $shoutRequest->validate();
        } catch (MaxLimitAchievedException $e) {
            return $response->withStatus(StatusCode::HTTP_BAD_REQUEST, $e->getMessage());
        }

        $found = $this->shoutService->getShoutQuotes($shoutRequest->getPerson(), $shoutRequest->getLimit());

        return $response
            ->getBody()
            ->write(json_encode($found, JSON_UNESCAPED_UNICODE));
    }
}