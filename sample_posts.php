<?php
include("vendor/autoload.php");

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;

class PostsDemo implements MessageComponentInterface {

	public $connections = [];
	public $subscriptions = [];

	public function subscribe($post_id, ConnectionInterface $conn) {

		$post_id = intval($post_id);
		$conn_id = spl_object_hash($conn);

		if(!isset($this->subscriptions[$post_id])) $this->subscriptions[$post_id] = [];
		if(!isset($this->connections[$conn_id])) $this->connections[$conn_id] = [];

		$this->subscriptions[$post_id][$conn_id] = $conn;

		echo "[SUB] Connection {$conn_id} subscribed to post {$post_id}\n";

	}


	public function broadcast($post_id, $data) {
		$post_id = intval($post_id);
		$msg = json_encode($data);

		if(!isset($this->subscriptions[$post_id])) return;

		echo "[BCAST >> {$post_id}] {$msg}\n";

		foreach($this->subscriptions[$post_id] as $conn) { /* @var $conn ConnectionInterface */
			$conn_id = spl_object_hash($conn);
			echo "\t[CLID::{$conn_id}] Sending... ";

			$conn->send($msg);

			echo "OK!\n";
		}
	}

	public function onMessage(ConnectionInterface $conn, $msg) {

		echo "[RECV] {$msg}\n";

		$data = json_decode($msg);

		switch($data->event) {

			case "subscribe":
				$this->subscribe($data->post_id, $conn);
				break;

			case "post_update":
				$this->onPostUpdate($conn, $data);
				break;

			default: return;
		}

	}

	public function onPostUpdate(ConnectionInterface $conn, $data) {
		if(!isset($data->post)) $conn->close();
		if($data->token != "my_secret_token") $conn->close();

		$this->broadcast($data->post->id, [
			'event' => 'post_update',
			'post' => $data->post,
		]);
	}

	public function onClose(ConnectionInterface $conn) {

		$conn_id = spl_object_hash($conn);

		if(!isset($this->connections[$conn_id])) return;

		foreach($this->connections[$conn_id] as $post_id) {
			unset($this->subscriptions[intval($post_id)][$conn_id]);
		}

		unset($this->connections[$conn_id]);

	}

	// ----------------------------------------

	public function onOpen(ConnectionInterface $conn) {}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "Error: {$e->getMessage()}\n";
		$conn->close();
	}
}

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new PostsDemo()
		)
	),
	8080
);

echo "Starting server on port 8080...\n";

$server->run();