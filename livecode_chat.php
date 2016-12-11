<?php
include("vendor/autoload.php");

// COLA :D
// Class < MessageComponentInterface: onOpen, onMessage, onClose; implement broadcast
// Server: IoServer::factory, HttpServer, WsServer, Class; $server->run

class ChatDemo implements \Ratchet\MessageComponentInterface {

	public $clients;

	public function __construct() {
		$this->clients = new SplObjectStorage();
	}

	function onOpen(\Ratchet\ConnectionInterface $conn) {
		$this->clients->attach($conn);
		echo "Connection opened... \n";
	}

	function onClose(\Ratchet\ConnectionInterface $conn) {
		$this->clients->detach($conn);
		echo "Connection closed... \n";
	}

	function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
		echo "Error! {$e->getMessage()}\n";
		$conn->close();
	}

	function onMessage(\Ratchet\ConnectionInterface $from, $msg) {

		echo "Message: {$msg}\n";

		$data = json_decode($msg);

		foreach($this->clients as $client) {
			$client->send(json_encode([
				'nickname' => $data->nickname,
				'message' => $data->message
			]));
		}

	}
}

$server = \Ratchet\Server\IoServer::factory(
	new \Ratchet\Http\HttpServer(
		new \Ratchet\WebSocket\WsServer(
			new ChatDemo()
		)
	), 8080
);

$server->run();