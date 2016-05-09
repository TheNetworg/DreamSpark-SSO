<?php
$app->error(function($e) use ($app) {
	$app->contentType('text/html');
	
	if(gettype($e) == "object" && get_class($e) == "ErrorException") {
		$app->response()->status(500);
		\Slim\ApplicationInsights::exception($e);
		baseHTML::header();
		?>
		<main class="Container">
			<div class="Content">
				<b>Something went wrong!</b> Please try again later or contact support at <a href="mailto:dreamspark@edulog.in">dreamspark@edulog.in</a>
			</div>
		</main>
		<?php
		baseHTML::footer();
	}
	else {
		$app->response()->status(400);
		baseHTML::header();
		?>
		<main class="Container">
			<div class="Content">
				<b>Error:</b> <?=$e?>
			</div>
		</main>
		<?php
		baseHTML::footer();
	}
	
	if(method_exists("\Slim\ApplicationInsights", "exception")) {
		\Slim\ApplicationInsights::exception($e);
	}
});