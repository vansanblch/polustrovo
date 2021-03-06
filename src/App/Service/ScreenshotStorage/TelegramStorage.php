<?php

namespace App\Service\ScreenshotStorage;

use App\Repository\ScreenshotBroadcastRepository;
use App\Service\Browshot\Response\ScreenshotResponse;
use App\Service\Notifier\TelegramNotifier;
use Projek\Slim\Monolog;

class TelegramStorage implements StorageInterface
{
    /**
     * @var TelegramNotifier
     */
    private $notifier;

    /**
     * @var ScreenshotBroadcastRepository
     */
    private $repository;

    /**
     * @var Monolog
     */
    private $logger;

    /**
     * TelegramStorage constructor.
     * @param TelegramNotifier $notifier
     * @param ScreenshotBroadcastRepository $repository
     * @param Monolog $logger
     */
    public function __construct(
        TelegramNotifier $notifier,
        ScreenshotBroadcastRepository $repository,
        Monolog $logger
    ) {
        $this->notifier = $notifier;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function getPriority(): int
    {
        return 10;
    }

    /**
     * Handle screenshot storing
     * @param ScreenshotResponse $response
     * @return bool
     */
    public function store(ScreenshotResponse $response): bool
    {
        $this->logger->debug('store to telegram');

        $data = [
            'target'        => $this->notifier->getChatId(),
            'screenshot_id' => $response->getScreenshotId() ?: null,
            'notifier'      => 'telegram',
        ];

        $this->logger->debug('data', $data);

        $result = null;
        if ($response->isStatusFinished() && $response->isSuccess()) {
            $result = $this->repository->insert($data);
        } else {
            $this->logger->debug('invalid response', [
                'status' => $response->status(),
                'code' => $response->code(),
                'error' => $response->error(),
            ]);
        }

        $this->logger->debug('end', [
            'result' => $result,
        ]);

        return $result > 0;
    }
}