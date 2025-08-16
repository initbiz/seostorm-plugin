<?php

declare(strict_types=1);

namespace Initbiz\SeoStorm\Jobs;

use Cache;
use Queue;

/**
 * Class that checks in cache if a similar job was already pushed to the queue
 * and is not pushing it once again
 */
class UniqueQueueJobDispatcher
{
    use \October\Rain\Support\Traits\Singleton;

    /**
     * Algorithm that's used to hash data and verify uniqueness of the job
     */
    public const HASH_ALGO = 'sha256';

    /**
     * Cache key that's storing all the values between requests
     */
    public const CACHE_KEY = 'initbiz-seostorm-pending-jobs';

    /**
     * Use this method to push job to queue, it will check for you if there's already
     * pending job for the following payload and class and returns true if it was added
     * successfully.
     *
     * @param string $jobClass
     * @param array|null $data
     * @return bool
     */
    public function push(string $jobClass, ?array $data = []): bool
    {
        if (empty($jobClass) || !class_exists($jobClass)) {
            throw new \Exception("Class " . $jobClass . " doesn't exist");
        }

        if ($this->isPending($jobClass, $data)) {
            return false;
        }

        $marked = $this->markAsPending($jobClass, $data);
        Queue::push($jobClass, $data);

        return $marked;
    }

    /**
     * Check if provided job is pending or not
     *
     * @param string $jobClass
     * @param array|null $data
     * @return boolean
     */
    public function isPending(string $jobClass, ?array $data = []): bool
    {
        $hash = hash(self::HASH_ALGO, json_encode($data));

        $pendingJobs = $this->getPendingJobsForClass($jobClass);

        return in_array($hash, $pendingJobs, true);
    }

    /**
     * Mark provided job as pending - remember to unmark it in your queue job once processed
     * It will return true if the job was marked as pending
     *
     * @param string $jobClass
     * @param array|null $data
     * @return bool
     */
    public function markAsPending(string $jobClass, ?array $data = []): bool
    {
        if ($this->isPending($jobClass, $data)) {
            return false;
        }

        $key = $this->getCacheKeyForClass($jobClass);
        $pendingJobsForClass = $this->getPendingJobsForClass($jobClass);
        $hash = hash(self::HASH_ALGO, json_encode($data));
        $pendingJobsForClass[] = $hash;

        $cacheData = Cache::get(self::CACHE_KEY);
        $cacheData[$key] = $pendingJobsForClass;
        Cache::put(self::CACHE_KEY, $cacheData, 600);

        return true;
    }

    /**
     * Unmark the provided job as pending - let it be pushed again
     *
     * @param string $jobClass
     * @param array|null $data
     * @return boolean
     */
    public function unmarkAsPending(string $jobClass, ?array $data = []): bool
    {
        if (!$this->isPending($jobClass, $data)) {
            return false;
        }

        $key = $this->getCacheKeyForClass($jobClass);
        $pendingJobsForClass = $this->getPendingJobsForClass($jobClass);
        $hash = hash(self::HASH_ALGO, json_encode($data));

        if (($arrayKey = array_search($hash, $pendingJobsForClass, true)) !== false) {
            unset($pendingJobsForClass[$arrayKey]);
        }

        $cacheData = Cache::get(self::CACHE_KEY);
        $cacheData[$key] = $pendingJobsForClass;
        Cache::put(self::CACHE_KEY, $cacheData, 600);

        return true;
    }

    /**
     * Get pending jobs for class
     *
     * @return array
     */
    public function getPendingJobsForClass(string $jobClass): array
    {
        $key = $this->getCacheKeyForClass($jobClass);

        $cacheData = Cache::get(self::CACHE_KEY);
        return $cacheData[$key] ?? [];
    }

    /**
     * Get cache key for the class
     *
     * @return string
     */
    public function getCacheKeyForClass(string $jobClass): string
    {
        return 'pending-jobs-' . str_slug($jobClass);
    }

    /**
     * Reset cache
     *
     * @return void
     */
    public function resetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
