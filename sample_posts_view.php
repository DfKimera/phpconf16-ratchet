<?php
$postID = intval($_GET['post_id']);
$path = "data/post_{$postID}.json";

if(!file_exists($path)) {
	die("Post does not exist: {$postID}");
}

$raw = file_get_contents($path);
$post = json_decode($raw);
?>

<!doctype html>
<html>
	<head>
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<script>

			var websocket;

			$(function() {

				websocket = new WebSocket("ws://127.0.0.1:8080/");

				websocket.onopen = function (ev) {
					websocket.send(JSON.stringify({
						'event': 'subscribe',
						'post_id': '<?= $postID ?>'
					}));

					$('#is-live').show();
				};

				websocket.onmessage = function (ev) {
					var data = JSON.parse(ev.data);

					console.log("Message received", data);

					if(data.event != "post_update") return;

					$('#post-title').text(data.post.title);
					$('#post-body').html(data.post.body.replace(/\n/g, '<br />'));
				};

				websocket.onclose = function (ev) {
					$('#is-live').hide();
				}

			});

		</script>
	</head>
	<body>
		<div class="container">

			<h1 id="post-title"><?= $post->title ?></h1> <span id="is-live" style="display: none" class="pull-right label label-success">LIVE!</span>
			<div class="well" id="post-body"><?= nl2br($post->body) ?></div>

		</div>
	</body>
</html>