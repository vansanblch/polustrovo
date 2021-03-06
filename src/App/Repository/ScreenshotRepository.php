<?php

namespace App\Repository;

use App\Entity\Screenshot;
use App\ScreenshotsDaily;
use App\Service\Browshot\Response\ScreenshotResponse;
use Carbon\Carbon;
use Doctrine\DBAL\Driver\PDOStatement;

class ScreenshotRepository extends Repository
{
    protected $tableName = 'screenshot';

    public function getLatest()
    {
        $sql = 'SELECT * FROM screenshot WHERE status = ? ORDER BY created_at DESC LIMIT 1';

        /** @var PDOStatement $stmt */
        $stmt = $this->getDb()->executeQuery($sql, [ScreenshotResponse::STATUS_FINISHED]);

        $image = $stmt->fetchObject(Screenshot::class);

        return $image;
    }

    public function getCurrentWeek()
    {
        $monday = Carbon::today()->startOfWeek();
        $sql = 'SELECT * FROM screenshot WHERE status = ? AND created_at >= ? ORDER BY created_at ASC';

        /** @var PDOStatement $stmt */
        $stmt = $this->getDb()->executeQuery($sql, [
            ScreenshotResponse::STATUS_FINISHED,
            $monday,
        ]);

        $result = $stmt->fetchAll(\PDO::FETCH_CLASS, Screenshot::class);

        return $result;
    }

    /**
     * @return ScreenshotsDaily[]
     */
    public function getDaily()
    {
        $sql = <<<SQL
SELECT group_concat(screenshot_id) AS ids, count(*) AS count, date(shooted_at) AS date
FROM screenshot
WHERE shooted_at IS NOT NULL
GROUP BY date(shooted_at)
SQL;

        /** @var PDOStatement $stmt */
        $stmt = $this->getDb()->executeQuery($sql);

        $result = $stmt->fetchAll(\PDO::FETCH_CLASS, ScreenshotsDaily::class);

        return $result;
    }

    public function getForDate($date)
    {
        $sql = 'SELECT * FROM screenshot WHERE status = ? AND created_at >= ? AND created_at < ? ORDER BY created_at ASC';

        $start = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $end = $start->copy()->addDay();

        /** @var PDOStatement $stmt */
        $stmt = $this->getDb()->executeQuery($sql, [ScreenshotResponse::STATUS_FINISHED, $start, $end]);

        $result = $stmt->fetchAll(\PDO::FETCH_CLASS, Screenshot::class);

        return $result;
    }

    public function getQueued()
    {
        $sql = <<<SQL
SELECT s.*
FROM screenshot s
WHERE
  (s.status NOT IN (?, ?))
  OR
  (s.status = ? AND s.error IS NOT NULL)
ORDER BY created_at ASC;
SQL;
        /** @var PDOStatement $stmt */
        $stmt = $this->getDb()->executeQuery($sql, [
            ScreenshotResponse::STATUS_FINISHED,
            ScreenshotResponse::STATUS_ERROR,
            ScreenshotResponse::STATUS_FINISHED,
        ]);

        /** @var Screenshot $screenshot */
        $screenshot = $stmt->fetchObject(Screenshot::class);

        return $screenshot;
    }

    /**
     * @param array $identifier
     * @return int
     */
    public function deleteBy(array $identifier)
    {
        return $this->getDb()->delete('screenshot', $identifier);
    }

    public function findByBrowshotId(string $browshotId)
    {
        $sql = <<<SQL
SELECT s.* FROM screenshot s WHERE s.browshot_id = ?;
SQL;
        /** @var PDOStatement $stmt */
        $stmt = $this->getDb()->executeQuery($sql, [
            $browshotId,
        ]);

        /** @var Screenshot $screenshot */
        $screenshot = $stmt->fetchObject(Screenshot::class);

        return $screenshot;
    }
}