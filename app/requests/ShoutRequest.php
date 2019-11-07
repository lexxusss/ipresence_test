<?php

namespace App\Requests;

use App\Exceptions\MaxLimitAchievedException;
use Slim\Http\Request;
use Slim\Http\StatusCode;

/**
 * Class ShoutRequest
 * @package App\Requests
 */
class ShoutRequest
{
    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var string
     */
    protected $person;

    /**
     * @var array
     */
    protected $settings;

    /**
     * ShoutRequest constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->settings = (require __DIR__ . "/../../src/settings.php")['settings'];

        // cast to integer if null or string, prevent using negative numbers
        $this->limit = max(intval($request->getQueryParam('limit')), 0);

        // cast to space separated lowercase
        $this->person = strtolower(trim(snakeCaseToSpaceCase($request->getAttribute('person'))));
    }

    /**
     * @throws MaxLimitAchievedException
     */
    public function validate()
    {
        $maxLimit = $this->settings['shout']['max_limit'];
        if ($this->limit > $maxLimit) {
            throw new MaxLimitAchievedException("Parameter limit must not be more than $maxLimit.", StatusCode::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit ?: $this->settings['shout']['max_limit'];
    }

    /**
     * @return string
     */
    public function getPerson(): string
    {
        return $this->person;
    }
}
