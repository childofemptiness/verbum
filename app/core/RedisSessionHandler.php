<?php

namespace App\Core;

use Predis\Client;
use SessionHandlerInterface;


class RedisSessionHandler implements SessionHandlerInterface {
    public $ttl = 1800;
    protected $redis;
    protected $prefix;

    public function __construct($prefix = 'PHPREDIS_SESSION:') {
        $this->redis = new Client(
             [
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379
        ]
        );
        $this->prefix = $prefix;
    }

    public function open($path, $name) :bool {
        return true;
    }

    public function close() : bool {
        $this->redis = null;
        unset($this->redis);
        return true;
    }
    public function read($id) :bool|string {
       $id = $this->prefix . $id;
       $sessData = $this->redis->get($id);
       $this->redis->expire($id, $this->ttl);
       return $sessData;
    }

    public function write($id, $data) :bool {
        $id = $this->prefix . $id;
        $this->redis->set($id, $data);
        $this->redis->expire($id, $this->ttl);
        return true;
    }

    public function destroy($id) :bool {
        $id = $this->prefix . $id;
        $this->redis->del($id);
        return true;
    }
    public function gc($max_lifetime) :bool {
        return true;
    }
}

