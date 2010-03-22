<?php
if(!defined('gearmandserver')) {
	define('gearmandserver', '192.168.1.100:1234,192.168.0.200,127.0.0.1');
}

/**
* super ultra simple un-hip Client for gearman.
* Just submit job.
* @author makoto tsuyuki
*/
class SimpleGearmanClient {
/***
SimpleGearmanClient::submit_job_bg('reverse', array("Hello world!"));
// reverse function is a sample worker that is distributed with gearmand(gearman/example/reverse_worker).
*/

	const DEFAULT_PORT = 4730; #Default gearmand listening port. Might'be 7003...
	const MAX_SERVERS = 5; # Max try count.
	const SOCKET_TIMEOUT = 0.5; # timeout by sec per server.
	
	/**
	* @param string $func function name.
	* @param array $args args passed to worker.
	* @return string $handle 
	*/
	static public function submit_job($func, $args) {
		$packet = self::create_request($func, 7, $args);
		// list($magic, $type, $data_length, $data) = self::send_request($packet);
		list($x, $x, $x, $handle) = self::send_request($packet);
		return $handle;
	}
	/**
	* @param string $func function name.
	* @param array $args args passed to worker.
	* @return none
	*/
	static public function submit_job_bg($func, $args) {
		$packet = self::create_request($func, 18, $args);
		self::send_request($packet);
	}
	
	
	static private function create_request($func, $type, $args) {
		$data = array();
		$data[] = $func;
		$data[] = md5(uniqid(rand(), true));
		$data[] = implode("\x00", $args);
		$data = implode("\x00", $data);
		$header = "\x00REQ".pack("NN", $type, strlen($data)); #BG
		return $header.$data;
	}
	static private function send_request($packet) {
		$socket = self::connect();
		$cmd_length = strlen($packet);
		$offset = 0;
		while($offset < $cmd_length) {
			$r = socket_write($socket, substr($packet, $offset, 64), 64);
			if($r === false) {
				throw new Exception("writing to $server:$port failed.");
			}
			$offset += $r;
		}
		$result = self::read_response($socket);
		socket_close($socket);
		return $result;
	}
	static private function read_response($socket) {
		$header = '';
		do {
			$bin = socket_read($socket, 12 - strlen($header));
			$header .= $bin;
		} while ($bin !== false && $bin !== '' && strlen($header) < 12);
		
		if ($bin === '' || empty($header)) {
			throw new Exception("No Response data.");
		}
		$r = @unpack('a4magic/Ntype/Nlength', $header);
		$magic = $r['magic'];
		$type  = $r['type'];
		$data_length = $r['length'];
		if($data_length > 0) {
			$data = '';
			while(strlen($data) < $data_length) {
				$data .= socket_read($socket, $data_length - strlen($data));
			}
		}
		return array($magic, $type, $data_length, $data);
	}
	static private function connect() {
		$timeout = self::SOCKET_TIMEOUT * 1000;
		$servers = explode(',', gearmandserver);
		$max_servers = (sizeof($servers) < self::MAX_SERVERS) ? sizeof($servers) : self::MAX_SERVERS;
		$picked_servers = array_rand($servers, $max_servers);
		if(!is_array($picked_servers)) {
			$picked_servers = array($picked_servers);
		}
		foreach($picked_servers as $svr) {
			$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
			try {
				list($server, $port) = explode(':', $servers[$svr]);
				if(empty($port)) $port = self::DEFAULT_PORT;
			
				// Trying to connect.
				socket_set_nonblock($socket);
				$connected = false;
				$attempts = 0;
				while(!($connected = @socket_connect($socket, $server, $port)) && $attempts++ < $timeout) {
					$error = socket_last_error(); 
					if($error === SOCKET_EISCONN) { //hmmmm.....
						$connected = true;
						break;
					} else if ($error !== SOCKET_EINPROGRESS && $error !== SOCKET_EALREADY) { 
						throw new Exception("ERR: server:$server port:$port reason:$error :". socket_strerror($error));
					}
					usleep(1000);
				}
				if(!$connected) {
					throw new Exception("ERR: server:$server port:$port reason:$error :". socket_strerror($error));
				}
				// Connected socket exists. Then set blocking-mode and return it.
				socket_set_block($socket);
				return $socket;
			} catch (Exception $e) {
				// timeout. Something wrong the server.
				try { socket_close($socket); } catch(Exception $e) {}
				print "$server:$port failed. go next server.\n";
			}
		}
		// all servers are timed out.
		throw new Exception("Can't connect to gearmand.");
	}
}


