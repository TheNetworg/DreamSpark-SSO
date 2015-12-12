<?php
if(!$app) die;

baseHTML::header();
baseHTML::navBar();
?>
<main class="Container">
	<div class="Content">
		<?php
		if($app->environment['slim.flash']->offsetGet("installResult") == "success") {
			?>
			<div class="notice success">The application was successfuly set up in your tenant.</div>
			<?php
		}
		else if($app->environment['slim.flash']->offsetGet("installResult") == "alreadyInstalled") {
			?>
			<div class="notice warning">The application is already installed in your tenant.</div>
			<?php
		}
		?>
		<p class="ms-font-xxl">Welcome to DreamSpark SSO</p>
		<p>In the tabs above, you can configure the application. Please see the <a href="https://go.thenetw.org/dreamsparksso-kb">knowledge base</a> for more information about setting this application up.</p>
		<p>In case you need help and can't find the answer in the Questions and Answers section of the <a href="https://go.thenetw.org/dreamsparksso-kb">knowledge base</a>, don't hesitate to <a href="mailto:dreamspark@edulog.in">contact us</a>.</p>
	</div>
</main>
<?php
baseHTML::footer();
?>