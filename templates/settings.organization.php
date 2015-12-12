<?php
if(!$app) die;

baseHTML::header();
baseHTML::navBar();

$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$organization = json_decode($settings->getPropertyValue("organization"));
?>
<main class="Container">
	<div class="Content">
		<?php
		if($app->environment['slim.flash']->offsetGet("updateSuccess")) {
			?>
			<div class="notice success">Your organization settings were successfuly updated!</div>
			<?php
		}
		else if($app->environment['slim.flash']->offsetGet("validationError")) {
			?>
			<div class="notice error">The logoutUri you entered isn't a valid URL.</div>
			<?php
		}
		?>
		<form class="Form" method="post">
			<div class="ms-TextField">
				<label class="ms-Label">Tenant</label>
				<p class="ms-TextField-field"><?=$tenantDomain?><p/>
			</div>
			<div class="ms-TextField">
				<label class="ms-Label">Signout URL</label>
				<input class="ms-TextField-field" type="text" placeholder="https://www.msn.com" name="logoutUri" value="<?=(isset($organization->logoutUri) ? $organization->logoutUri : "")?>" />
				<span class="ms-TextField-description">The website which users get redirected to after sucessful signout. This can be your insitution's home page for example.</span>
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