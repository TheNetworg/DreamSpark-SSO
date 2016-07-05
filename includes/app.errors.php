<?php
$app->error(function($e) use ($app) {
	$app->contentType('text/html');
	
	if(gettype($e) == "object" && get_class($e) == "ErrorException") {
		$app->response()->status(500);
		\Slim\ApplicationInsights::exception($e);
		?>
		<main class="Container">
			<div class="Content">
				<b>Something went wrong!</b> Please try again later or contact support at <a href="mailto:dreamspark@edulog.in">dreamspark@edulog.in</a>
			</div>
		</main>
		<?php
		
		if(method_exists("\Slim\ApplicationInsights", "exception")) {
			\Slim\ApplicationInsights::exception($e);
		}
	}
	else {
		$app->response()->status(400);
		if(gettype($e) == "object") $e = $e->getMessage();
		$app->render('error.twig', [
			'message' => $e
		]);
	}
});