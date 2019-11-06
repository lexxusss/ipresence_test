<?php


namespace App\service;


use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use ReflectionException;


/**
 * Class ShoutService
 * @package App\service
 */
class ShoutService
{
    /**
     * @var string
     */
    protected $serverUri;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    protected $cacheService;

    /**
     * ShoutService constructor.
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidArgumentException
     * @throws PhpfastcacheInvalidConfigurationException
     * @throws ReflectionException
     */
    public function __construct()
    {
        $this->serverUri = 'https://raw.githubusercontent.com/iPresence/backend_test/master/quotes.json';

        $cacheConfig = new ConfigurationOption();
        $cacheConfig->setDefaultChmod(755);
        $cacheConfig->setDefaultTtl(3000); // 50 min
        $cacheConfig->setPath(__DIR__ . '/../../storage/');

        $this->cacheService = CacheManager::getInstance("files", $cacheConfig);
    }


    /**
     * @param $person
     * @param $limit
     * @return array
     * @throws PhpfastcacheInvalidArgumentException
     */
    public function getShoutQuotes($person, $limit)
    {
        $cacheItem = $this->cacheService->getItem($person);

        if ($found = $cacheItem->get()) {
            return array_slice($found, 0, $limit);
        }

        $found = $this->askServer($person, $limit);

        $cacheItem->set($found)->expiresAfter(60);
        $this->cacheService->save($cacheItem);

        return $found;
    }

    /**
     * @param $person
     * @param $limit
     * @return array
     */
    protected function askServer($person, $limit)
    {
        $found = [];
        $serverData = json_decode(file_get_contents($this->serverUri), JSON_OBJECT_AS_ARRAY);
        $quotes = $serverData['quotes'];
        foreach ($quotes as $quoteData) {
            if ($limit && strtolower(trim($quoteData['author'])) == $person) {
                $found[] = rtrim(mb_strtoupper($quoteData['quote']), '.!') . '!';
                $limit--;
            }
        }

        return $found;
    }
}