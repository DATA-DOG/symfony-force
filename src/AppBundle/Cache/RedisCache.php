<?php

namespace AppBundle\Cache;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Validator\Mapping\Cache\CacheInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Process\Process;

class RedisCache extends CacheProvider implements CacheInterface
{
    /**
     * @var \Predis\Client|\Redis
     */
    protected $redis;

    protected $ns;

    public function __construct($redis, $ns)
    {
        $this->redis = $redis;
        $this->setNamespace($this->ns = $ns);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        $result = $this->redis->get($id);

        return null === $result ? false : unserialize($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return (bool) $this->redis->exists($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = false)
    {
        if (0 < $lifeTime) {
            $result = $this->redis->setex($id, (int) $lifeTime, serialize($data));
        } else {
            $result = $this->redis->set($id, serialize($data));
        }

        return (bool) $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return (bool) $this->redis->del($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return (bool) $this->redis->flushDB();
    }

    public function flushDB()
    {
        return (bool) $this->redis->flushDB();
    }

    public function flushNamespacedKeys()
    {
        // redis cli connection string
        $conn = sprintf("redis-cli -h %s -p %s -n %s --raw", $this->redis->getHost(), $this->redis->getPort(), $this->redis->getDBNum());
        // remove namespaced keys only
        $proc = new Process(sprintf("%s KEYS '*%s*' | xargs --delim='\\n' %s DEL", $conn, $this->ns, $conn));
        $proc->run();
        return $proc->getOutput();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $stats = $this->redis->info();

        return array(
            Cache::STATS_HITS => isset($stats['keyspace_hits']) ? $stats['keyspace_hits'] : $stats['Stats']['keyspace_hits'],
            Cache::STATS_MISSES => isset($stats['keyspace_misses']) ? $stats['keyspace_misses'] : $stats['Stats']['keyspace_misses'],
            Cache::STATS_UPTIME => isset($stats['uptime_in_seconds']) ? $stats['uptime_in_seconds'] : $stats['Server']['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE => isset($stats['used_memory']) ? $stats['used_memory'] : $stats['Memory']['used_memory'],
            Cache::STATS_MEMORY_AVAILIABLE => null,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function has($class)
    {
        return $this->contains($class);
    }

    /**
     * {@inheritdoc}
     */
    public function read($class)
    {
        return $this->fetch($class);
    }

    /**
     * {@inheritdoc}
     */
    public function write(ClassMetadata $metadata)
    {
        $this->save($metadata->getClassName(), $metadata);
    }
}
