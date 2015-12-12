<?php
namespace Slim;

use API;

class ApplicationInsights {
	private static $instrumenetationKey;
	private static $telemetryClient;
	
	public static function init() {
		global $app;
		
		self::$instrumenetationKey = getenv("INSTRUMENTATION_KEY");
		if(empty(self::$instrumenetationKey)) return false;
		
		self::$telemetryClient = new \ApplicationInsights\Telemetry_Client();
		self::$telemetryClient->getContext()->setInstrumentationKey(self::$instrumenetationKey);
		
		if(!empty(session_id())) self::$telemetryClient->getContext()->getSessionContext()->setId(session_id());
		
		$app->hook('slim.after', function() {
			global $app;
			
			$url = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			self::$telemetryClient->trackRequest(
				'application',
				$url,
				$_SERVER["REQUEST_TIME_FLOAT"],
				self::getRequestDuration(),
				$app->response->getStatus(),
				self::isRequestSuccessful(),
				self::getRequestProperties(),
				null
			);
			self::$telemetryClient->flush();
		}, 100);
		//TODO: track page view duration on slim.start hook
		//TODO: store last view into flash message
		
		return true;
	}
	public static function telemetryEnabled() {
		return isset(self::$telemetryClient);
	}
	public static function exception(\Exception $exception) {
		if(self::telemetryEnabled() && $exception) {
			self::$telemetryClient->trackException($exception, self::getRequestProperties(true, true));
			self::$telemetryClient->flush();
		}
	}
	private static function getRequestDuration() {
		return (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000;
	}
	private static function isRequestSuccessful() {
		global $app;
		
		return ($app->response->getStatus() < 400);
	}
	private static function getRequestProperties($session = false, $url = false) {
		$properties = [];
		$properties['ip'] = $_SERVER["REMOTE_ADDR"];
		if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			$properties['cloudflare'] = true;
			$properties['ip'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
			$properties['country'] = $_SERVER["HTTP_CF_IPCOUNTRY"];
		}
		else {
			$properties['cloudflare'] = false;
		}
		if(!empty($_SERVER["HTTPS"])) $properties['secure'] = true;
		if($url) $properties['url'] = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		
		if(isset($_SESSION["API"]["cache"]["GRAPH:me"])) {
			$me = $_SESSION["API"]["cache"]["GRAPH:me"];
			$properties['userPrincipalName'] = $me->content['userPrincipalName'];
			$properties['objectId'] = $me->content['objectId'];
			$properties['tenant'] = API::getTenantDomain();
		}
		
		return $properties;
	}
	public static function insertJS() {
		if(self::telemetryEnabled()) {
			?>
			<script type="text/javascript">
				var appInsights=window.appInsights||function(config){
					function s(config){t[config]=function(){var i=arguments;t.queue.push(function(){t[config].apply(t,i)})}}var t={config:config},r=document,f=window,e="script",o=r.createElement(e),i,u;for(o.src=config.url||"//az416426.vo.msecnd.net/scripts/a/ai.0.js",r.getElementsByTagName(e)[0].parentNode.appendChild(o),t.cookie=r.cookie,t.queue=[],i=["Event","Exception","Metric","PageView","Trace"];i.length;)s("track"+i.pop());return config.disableExceptionTracking||(i="onerror",s("_"+i),u=f[i],f[i]=function(config,r,f,e,o){var s=u&&u(config,r,f,e,o);return s!==!0&&t["_"+i](config,r,f,e,o),s}),t
						}({
					instrumentationKey: "<?=self::$instrumenetationKey?>"
						});
			
				window.appInsights=appInsights;
				appInsights.trackPageView();
			</script>
			<?php
		}
	}
	public static function event($eventName, $eventProperties = NULL, $measurements = NULL) {
		$properties = self::getRequestProperties();
		if($eventProperties) $properties += $eventProperties;
		self::$telemetryClient->trackEvent(
			$eventName,
			$properties,
			$measurements
		);
	}
}