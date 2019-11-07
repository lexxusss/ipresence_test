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
     * @var string
     */
    protected $cacheFolder;

    /**
     * @var float|int
     */
    protected $cacheTime;

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
        $this->cacheTime = 60 * 60 * 24;
        $this->cacheFolder = __DIR__ . '/../../storage/';
        if (!file_exists($this->cacheFolder)) {
            mkdir($this->cacheFolder, 0755, true);
        } elseif (substr(sprintf('%o', fileperms($this->cacheFolder)), -4) != 0755) {
            chmod($this->cacheFolder, 0755);
        }

        $cacheConfig = new ConfigurationOption();
        $cacheConfig->setDefaultChmod(0755);
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

        if (!($found = $cacheItem->get())) {
            $found = $this->askServer($person);

            $cacheItem->set($found)->expiresAfter($this->cacheTime);
            $this->cacheService->save($cacheItem);
        }

        return array_slice($found, 0, $limit);
    }

    /**
     * @param $person
     * @return array
     */
    protected function askServer($person)
    {
        $found = [];
        $serverData = json_decode(file_get_contents($this->serverUri), JSON_OBJECT_AS_ARRAY);
        $quotes = $serverData['quotes'];
        foreach ($quotes as $quoteData) {
            if (strtolower(trim($quoteData['author'], "-_– \t\n\r\0\x0B")) == $person) {
                $found[] = rtrim(mb_strtoupper($quoteData['quote']), '.!') . '!';
            }
        }

        return $found;
    }
}