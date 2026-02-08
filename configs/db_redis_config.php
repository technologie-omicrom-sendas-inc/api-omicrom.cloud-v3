<?php
// Load the Redis extension
if (!extension_loaded('redis')) {
    die('Redis extension not loaded. Please install and enable the Redis extension.');
}

// Create a new Redis instance
if (!function_exists('connection_db_redis')) {
    function connection_db_redis($db_redis) {
        $redis = new Redis();
        try {
            $omicrom_Redis_db_config = [
                "_user_admin"=>"",
                "_db_pass"=>"",
                "_ip"=>"",
                "_port"=>6379,
            ];
            // Connect to the Redis server
            $redis_user_admin = $omicrom_Redis_db_config["_user_admin"];
            $redis_db_pass = $omicrom_Redis_db_config["_db_pass"];
            $redis_ip = $omicrom_Redis_db_config["_ip"];
            $redis_port = $omicrom_Redis_db_config["_port"];
            
        
            // Parameters: host, port, timeout
            $redis->connect($redis_ip, $redis_port, 2.5);
            // Authenticate if your Redis server requires a password
            $redis->auth($redis_db_pass);
    
            // Select Redis database number e.g. 0, 1, ...15
            $redis->select($db_redis);
    
            // Check if the connection is successful
            if ($redis->ping()) {
                /// echo "Connected to Redis server successfully on db".$db_redis. "!<br>";
            }
            return $redis;
        } catch (RedisException $e) {
            // Handle connection errors
            die("Redis connection failed: " . $e->getMessage());
        }
    }
}
