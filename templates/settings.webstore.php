<?php
if(!$app) die;

baseHTML::header();
baseHTML::navBar();

$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$webstore = json_decode($settings->getPropertyValue("webstore"));
?>
<main class="Container">
	<div class="Content">
		<?php
		if($app->environment['slim.flash']->offsetGet('updateSuccess')) {
			?>
			<div class="notice success">Your Webstore settings were sucessfully updated!</div>
			<?php
		}
		else if($app->environment['slim.flash']->offsetGet('validationError')) {
			?>
			<div class="notice error">You have to fill out both your Webstore account number and key!</div>
			<?php
		}
		?>
		<form class="Form" method="post">
			<div class="ms-TextField is-required">
				<label class="ms-Label">Organization Account Number</label>
				<input class="ms-TextField-field" type="text" name="account" value="<?=(isset($webstore->account) ? $webstore->account : "")?>"/>
				<span class="ms-TextField-description">The ID of your DreamSpark Premium subscription.</span>
			</div>
			<div class="ms-TextField is-required">
				<label class="ms-Label">Key</label>
				<input class="ms-TextField-field" type="text" name="key" value="<?=(isset($webstore->key) ? $webstore->key : "")?>"/>
				<span class="ms-TextField-description">The key you generated during the configuration of SSO process.</span>
			</div>
			<div class="SubmitButton">
				<button class="ms-Button ms-Button--primary" type="submit"><span class="ms-Button-label">Save</span></button>
			</div>
		</form>
	</div>
</main>
<?php
baseHTML::footer();
?>