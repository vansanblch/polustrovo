<?php
/**
 * Created by PhpStorm.
 * User: inikulin
 * Date: 14.03.17
 * Time: 22:09
 */

namespace App\Service\ScreenshotStorage;


use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidFactoryInterface;

class DoctrineStorage implements StorageInterface
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var UuidFactoryInterface
     */
    private $uuidFactory;

    public function __construct(Connection $conn, UuidFactoryInterface $uuidFactory)
    {
        $this->conn = $conn;
        $this->uuidFactory = $uuidFactory;
    }

    /**
     * Handle screenshot storing
     *
     * @param \stdClass $data
     * @return bool
     */
    public function store(\stdClass $data)
    {
        $now = Carbon::now()->toDateTimeString();

        $result = $this->conn->insert('screenshot', [
            'screenshot_id' => $this->uuidFactory->uuid4()->toString(),
            'path'          => $data->path,
            'shooted_at'    => $now,
            'created_at'    => $now,
        ]);

        return $result > 0;
    }
}