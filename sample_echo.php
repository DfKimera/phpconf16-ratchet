<?php
include("vendor/autoload.php");

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;

class EchoDemo implements MessageComponentInterface {

	public function onOpen(ConnectionInterface $conn) {
		echo "Connection open: {$conn->resourceId}\n";
	}

	public function onMessage(ConnectionInterface $from, $msg) {
		echo "Received: {$from->resourceId}\n";
		$from->send( "{$msg} -> " . str_rot13($msg) );
	}

	public function onClose(ConnectionInterface $conn) {
		echo "Connection closed: {$conn->resourceId}\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "Error: {$conn->resourceId}\n";
	}
}

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new EchoDemo()
		)
	),
	8080
);

echo "Starting server on port 8080...\n";

$server->run();