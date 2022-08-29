<?php

namespace Drupal\smartweather\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class ClientLocation
 * @package Drupal\smartweather\Services
 */
class ClientLocation {

  /**
   * @var ClientInterface
   */
  private $http_client;

  /**
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  private $request;

  /**
   * @var CacheBackendInterface
   */
  private $cache;

  public function __construct(RequestStack $requestStack, ClientInterface $client, CacheBackendInterface $cache) {
    $this->request = $requestStack->getCurrentRequest();
    $this->http_client = $client;
    $this->cache = $cache;
  }

  public function get_client_location($client_ip = '') {

    // Do not get location details for bots
    $user_agent = $this->get_user_agent();
    $bot_regex_pattern = "(googlebot\/|Googlebot\-Mobile|Googlebot\-Image|Google favicon|Mediapartners\-Google|bingbot|slurp|java|wget|curl|Commons\-HttpClient|Python\-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|jyxobot|FAST\-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub\.org|UsineNouvelleCrawler|antibot|netresearchserver|speedy|fluffy|bibnum\.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|IOI|ips\-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail\.RU_Bot|discobot|heritrix|findthatfile|europarchive\.org|NerdByNature\.Bot|sistrix crawler|ahrefsbot|Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb\-spider|sogou|lssbot|careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|openindexspider|gnam gnam spider|web\-archive\-net\.com\.bot|backlinkcrawler|coccoc|integromedb|content crawler spider|toplistbot|seokicks\-robot|it2media\-domain\-crawler|ip\-web\-crawler\.com|siteexplorer\.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki\-bot|CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|g00g1e\.net|GrapeshotCrawler|urlappendbot|brainobot|fr\-crawler|binlar|SimpleCrawler|Livelapbot|Twitterbot|cXensebot|smtbot|bnf\.fr_bot|A6\-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive\.org_bot|Applebot|TweetmemeBot|crawler4j|findxbot|SemrushBot|yoozBot|lipperhey|y!j\-asr|Domain Re\-Animator Bot|AddThis|YisouSpider|BLEXBot|YandexBot|SurdotlyBot|AwarioRssBot|FeedlyBot|Barkrowler|Gluten Free Crawler|Cliqzbot)";
    $check_user_agent = preg_match("/{$bot_regex_pattern}/", $user_agent);

    // if website visitor is not a bot
    if (!$check_user_agent) {

      if ($client_ip == "") {
        $client_ip = $this->get_client_ip();
      }

      if ($client_ip == "UNKNOWN") {
        $location_data = array("error_data" => t("IP address can not be detected."));
      } else {
          // get data from cache if exist
          $cid = 'openweatherip:' . $client_ip;
          $cached_data = $this->cache->get($cid);

          // make a request to geoplugin to get data if not cached
          if (!$cached_data) {
            // Use http client to get location details from geoplugin.com
            $geoplugin_endpoint = "http://www.geoplugin.net/php.gp?ip=" . $client_ip;

            $options['http_errors'] = FALSE;
            $response = $this->http_client->get($geoplugin_endpoint, $options);

            if ($response->getStatusCode() != 200) {
              $location_data = array("error_data" => t("Location details can not be fetched from Geoplugin.com."));
            } else {
              $location_data = unserialize($response->getBody()->getContents());
              $this->cache->set($cid, $location_data, \Drupal::time()->getRequestTime() + (3600));
            }
          }
          else {
            $location_data = $cached_data->data;
          }
      }
    }
    else {
      $location_data = array("error_data" => t("Location details can not be fetched for a bot."));
    }

    return $location_data;
  }

  /**
   * Get the client IP address
   * @return mixed|string
   */
  function get_client_ip() {

    $ipaddress = '';

    if($this->request->server->get('HTTP_X_FORWARDED_FOR')) {
      $ipaddress = $this->request->server->get('HTTP_X_FORWARDED_FOR');
    }
    else if($this->request->server->get('HTTP_X_FORWARDED')) {
      $ipaddress = $this->request->server->get('HTTP_X_FORWARDED');
    }
    else if($this->request->server->get('HTTP_FORWARDED_FOR')) {
      $ipaddress = $this->request->server->get('HTTP_FORWARDED_FOR');
    }
    else if($this->request->server->get('HTTP_FORWARDED')) {
      $ipaddress = $this->request->server->get('HTTP_FORWARDED');
    }
    else if($this->request->server->get('REMOTE_ADDR')) {
      $ipaddress = $this->request->server->get('REMOTE_ADDR');
    }
    else {
      $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
  }

  public function get_user_agent() {
    $user_agent = $this->request->server->get('HTTP_USER_AGENT');
    return $user_agent;
  }

}
