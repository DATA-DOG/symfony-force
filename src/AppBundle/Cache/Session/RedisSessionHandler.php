<?php

namespace AppBundle\Cache\Session;

use Predis\Client;

class RedisSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var integer Default PHP max execution time in seconds
     */
    const DEFAULT_MAX_EXECUTION_TIME = 30;

    /**
     * @var boolean Indicates an sessions should be locked
     */
    private $locking;

    /**
     * @var boolean Indicates an active session lock
     */
    private $locked;

    /**
     * @var string Session lock key
     */
    private $lockKey;

    /**
     * @var integer Microseconds to wait between acquire lock tries
     */
    private $spinLockWait;

    /**
     * @var integer Maximum amount of seconds to wait for the lock
     */
    private $lockMaxWait;

    /**
     * Redis session storage constructor
     *
     * @param Client $redis     Redis database connection
     * @param array $options    Session options
     * @param string $prefix    Prefix to use when writing session data
     */
    public function __construct(Client $redis, array $options = array(), $prefix = 'sess', $locking = true, $spinLockWait = 150000)
    {
        $this->redis = $redis;
        $this->ttl = isset($options['cookie_lifetime']) ? (int) $options['cookie_lifetime'] : 3600 /* 1h */;
        $this->prefix = $prefix;

        $this->locking = $locking;
        $this->locked = false;
        $this->lockKey = null;
        $this->spinLockWait = $spinLockWait;
        $this->lockMaxWait = ini_get('max_execution_time');
        if (!$this->lockMaxWait) {
            $this->lockMaxWait = self::DEFAULT_MAX_EXECUTION_TIME;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }


    /**
     * @param string $sessionId
     */
    private function lockSession($sessionId)
    {
        $attempts = (1000000 / $this->spinLockWait) * $this->lockMaxWait;

        $this->lockKey = $sessionId . '.lock';
        for ($i = 0; $i < $attempts; $i++) {
            $success = $this->redis->setnx($this->prefix . $this->lockKey, '1');
            if ($success) {
                $this->locked = true;
                $this->redis->expire($this->prefix . $this->lockKey, $this->lockMaxWait + 1);
                return true;
            }
            usleep($this->spinLockWait);
        }

        return false;
    }

    private function unlockSession()
    {
        $this->redis->del($this->prefix . $this->lockKey);
        $this->locked = false;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        if ($this->locking) {
            if ($this->locked) {
                $this->unlockSession();
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {
        if ($this->locking) {
            if (!$this->locked) {
                if (!$this->lockSession($sessionId)) {
                    return false;
                }
            }
        }

        return $this->redis->get($this->getRedisKey($sessionId)) ?: '';
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        if ($this->locking) {
            if (!$this->locked) {
                if (!$this->lockSession($sessionId)) {
                    return false;
                }
            }
        }

        $this->redis->setex($this->getRedisKey($sessionId), $this->ttl, $data);
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        $this->redis->del($this->getRedisKey($sessionId));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Change the default TTL
     *
     * @param int $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * Prepends the session ID with a user-defined prefix (if any).
     * @param string $sessionId session ID
     *
     * @return string prefixed session ID
     */
    protected function getRedisKey($sessionId)
    {
        if (empty($this->prefix)) {
            return $sessionId;
        }

        return $this->prefix . ':' . $sessionId;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}
