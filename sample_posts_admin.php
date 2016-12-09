<?php
include("vendor/autoload.php");

$postID = intval($_GET['post_id']);
$path = "data/post_{$postID}.json";

if(isset($_POST['title'])) {

	$post = [
		'id' => $postID,
		'title' => $_POST['title'],
		'body' => $_POST['body']
	];

	$raw = json_encode($post);
	file_put_contents($path, $raw);


	$client = new \WebSocket\Client("ws://192.168.10.1:8080/");
	$client->send(json_encode([
		'event' => 'post_update',
		'post' => $post,
		'token' => 'my_secret_token'
	]));

}

$raw = file_exists($path) ?
	file_get_contents($path) :
	json_encode(['id' => $postID, 'title' => '', 'body' => '']);

$post = json_decode($raw);
?>

<!doctype html>
<html>
	<head>
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="css/bootstrap.min.css" />
	</head>
	<body>
		<div class="container">

			<form class="col-md-12" name="editPost" method="POST">
				<div class="panel panel-info">
					<div class="panel-heading clearfix">
						Editar post
					</div>

					<?php if(isset($_POST['title'])) { ?>
						<div class="alert alert-success">Post salvo com sucesso!</div>
					<?php } ?>

					<div class="panel-body" id="messages">
						<div class="form-group">
							<label class="control-label" for="fld-title">Título:</label>
							<input class="form-control" type="text" name="title" id="fld-title" value="<?= $post->title ?>" />
						</div>
						<div class="form-group">
							<label class="control-label" for="fld-body">Post:</label>
							<textarea class="form-control" style="height: 500px" name="body" id="fld-body"><?= $post->body ?></textarea>
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-primary">Salvar</button>
						</div>
					</div>
				</div>
			</form>

		</div>
	</body>
</html>