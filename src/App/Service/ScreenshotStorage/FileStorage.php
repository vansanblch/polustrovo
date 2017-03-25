<?php

namespace App\Service\ScreenshotStorage;

use App\Service\Browshot\Response\ScreenshotResponse;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class FileStorage implements StorageInterface
{
    /**
     * @var string
     */
    private $dir;

    /**
     * EloquentStorage constructor.
     * @param $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function getName()
    {
        return 'file';
    }

    /**
     * @param string $key
     * @param ScreenshotResponse $response
     * @return bool
     */
    public function store(string $key, ScreenshotResponse $response): bool
    {
        if (ScreenshotResponse::STATUS_FINISHED !== $response->get('status')) {
            return false;
        }

        $result = false;

        $path = $this->dir.'/'.$key;

        $client = new Client();
        $promise = $client->requestAsync('GET', $response->get('screenshot_url'));
        $promise->then(function (ResponseInterface $res) use ($path, $result) {
            if (200 === $res->getStatusCode()) {
                $content = $res->getBody()->getContents();
                if (file_put_contents($path, $content)) {
                    $result = true;
                }
            }

            return $result;
        });
        $promise->wait();

        return $result;
    }
}