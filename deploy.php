<?php
//if(PHP_SAPI !== 'cli') die('Server side only.');

require '../vendor/autoload.php';

use Assetic\AssetManager;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\CacheBustingWorker;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\HttpAsset;
use Assetic\AssetWriter;

$am = new AssetManager();
$am->set('base_scripts', new GlobAsset('assets/js/*'));
$am->set('base_styles', new GlobAsset('assets/css/*'));

$factory = new AssetFactory('/assets/cache/');
$factory->setAssetManager($am);
$factory->setDebug(true);
$factory->addWorker(new CacheBustingWorker());

$js = $factory->createAsset([
	'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js',
	'https://controls.office.com/appChrome/1.0/Office.Controls.AppChrome.js',
	'https://controls.office.com/people/1.0/Office.Controls.People.js',
	'https://raw.githubusercontent.com/OfficeDev/Office-UI-Fabric/release/1.1.0/dist/js/jquery.fabric.min.js',
	'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js',
	'@base_scripts'
], [], ["output" => "app.js"]);

$css = $factory->createAsset([
	'https://controls.office.com/appChrome/1.0/Office.Controls.AppChrome.min.css',
	'https://controls.office.com/people/1.0/Office.Controls.People.min.css',
	'https://raw.githubusercontent.com/OfficeDev/Office-UI-Fabric/release/1.1.0/dist/css/fabric.min.css',
	'https://raw.githubusercontent.com/OfficeDev/Office-UI-Fabric/release/1.1.0/dist/css/fabric.components.min.css',
	'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css',
	'@base_styles'
], [], ["output" => "app.css"]);
$writer = new AssetWriter('assets/cache/');

$writer->writeAsset($js);
echo "Generated ".$js->getTargetPath().PHP_EOL;

$writer->writeAsset($css);
echo "Generated ".$css->getTargetPath().PHP_EOL;

$cache = [];
$cache["js"] = $js->getTargetPath();
$cache["css"] = $css->getTargetPath();
file_put_contents("assets/cache/cache.json", json_encode($cache));

foreach(glob("assets/cache/*") as $file) {
	if(!in_array(basename($file), [$cache["js"], $cache["css"], "cache.json"])) {
		unlink($file);
	}
}
echo "Done".PHP_EOL;
?>