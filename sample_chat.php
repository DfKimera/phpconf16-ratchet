<?php
include("vendor/autoload.php");

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;

class SimpleChatDemo implements MessageComponentInterface {

	public $connections;

	public function __construct() {
		$this->connections = new SplObjectStorage();
	}

	public function broadcast($data) {
		foreach($this->connections as $conn) {
			$conn->send(json_encode($data));
		}
	}

	public function onOpen(ConnectionInterface $conn) {
		$this->connections->attach($conn);
		$this->broadcast(['event' => 'join']);
	}

	public function onMessage(ConnectionInterface $from, $msg) {

		$data = json_decode($msg);

		$this->broadcast(['event' => 'message', 'nickname' => $data->nickname, 'message' => $data->message]);
	}

	public function onClose(ConnectionInterface $conn) {
		$this->connections->detach($conn);
		$this->broadcast(['event' => 'left']);
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "Error: {$e->getMessage()}\n";
		$conn->close();
	}
}

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new SimpleChatDemo()
		)
	),
	8080
);

echo "Starting server on port 8080...\n";

$server->run();