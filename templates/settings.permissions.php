<?php
if(!$app) die;

baseHTML::header();
baseHTML::navBar();

$tenantDomain = API::getTenantDomain();
$settings = appStorage::getSettings($tenantDomain);
$access = $settings->getPropertyValue("access");
$accessGroups = json_decode($settings->getPropertyValue("accessGroups"));
?>
<main class="Container">
	<div class="Content">
		<?php
		if($app->environment['slim.flash']->offsetGet("updateSuccess")) {
			?>
			<div class="notice success">Your permissions were successfuly updated! If you changed permissions, please note that it may take up to 12 hours for users to see the application in their app launcher.</div>
			<?php
		}
		else if($app->environment['slim.flash']->offsetGet("validationError")) {
			?>
			<div class="notice error">Something went wrong! Please refresh the page and try again.</div>
			<?php
		}
		?>
		<form class="Form" method="post">
			<div class="ms-ChoiceFieldGroup">
				<div class="ms-ChoiceFieldGroup-title">
					<label class="ms-Label is-required">Select who will have access to DreamSpark Premium:</label>
					<span class="ms-TextField ms-TextField-description">Please note that if you select everyone, make sure that you filter access to DreamSpark on a different level (for example <a href="https://azure.microsoft.com/en-us/documentation/articles/active-directory-accessmanagement-group-saasapps">Assigned Access in Azure Active Directory</a>) else you might be in violation with DreamSpark's Terms of Service.</span>
				</div>
				<div class="ms-ChoiceField">
					<input id="radio1-a" class="ms-ChoiceField-input" type="radio" name="access" value="everyone" <?=($access == "everyone" ? "checked" : "")?> />
					<label for="radio1-a" class="ms-ChoiceField-field"><span class="ms-Label">Everyone</span>
					</label>
				</div>
				<div class="ms-ChoiceField">
					<input id="radio1-b" class="ms-ChoiceField-input" type="radio" name="access" value="groups" <?=($access == "groups" ? "checked" : "")?> />
					<label for="radio1-b" class="ms-ChoiceField-field"><span class="ms-Label">Specific groups</span>
					</label>
				</div>
			</div>
			<div id="groups">
				<div class="ms-TextField">
					<label class="ms-Label">Students</label>
					<select class="ms-TextField-field group-selection" name="students[]" multiple>
						<?php
						foreach($accessGroups->students as $objectId) {
							$group = API::getGroup($objectId);
							if($group) {
								?>
								<option value="<?=$group['objectId']?>" selected><?=$group['displayName']?></option>
								<?php
							}
						}
						?>
					<select/>
					<span class="ms-TextField-description">Security group which contains all student accounts.</span>
				</div>
				<div class="ms-TextField">
					<label class="ms-Label">Teachers</label>
					<select class="ms-TextField-field group-selection" name="faculty[]" multiple>
						<?php
						foreach($accessGroups->faculty as $objectId) {
							$group = API::getGroup($objectId);
							if($group) {
								?>
								<option value="<?=$group['objectId']?>" selected><?=$group['displayName']?></option>
								<?php
							}
						}
						?>
					<select/>
					<span class="ms-TextField-description">Security group which contains all teacher/faculty accounts.</span>
				</div>
				<div class="ms-TextField">
					<label class="ms-Label">Staff</label>
					<select class="ms-TextField-field group-selection" name="staff[]" multiple>
						<?php
						foreach($accessGroups->staff as $objectId) {
							$group = API::getGroup($objectId);
							if($group) {
								?>
								<option value="<?=$group['objectId']?>" selected><?=$group['displayName']?></option>
								<?php
							}
						}
						?>
					<select/>
					<span class="ms-TextField-description">Security group which contains all staff accounts.</span>
				</div>
			</div>
			<div class="SubmitButton">
				<button class="ms-Button ms-Button--primary" type="submit"><span class="ms-Button-label">Save</span></button>
			</div>
		</form>
	</div>
</main>
<script>
	$(document).ready(function() {
		DreamSparkSSO.Settings.Permissions.init();
	});
</script>
<?php
baseHTML::footer();
?>