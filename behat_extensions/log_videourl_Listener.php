<?php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogVideoUrlListener implements EventSubscriberInterface
{
  private $mink;
  private $parameters;
  private $wd_host;

  public function __construct(Behat\Mink\Mink $mink, array $parameters, $wd_host)
  {
    $this->mink       = $mink;
    $this->parameters = $parameters;
    $this->wd_host = $wd_host;
  }

  public static function getSubscribedEvents()
  {
    return array('afterSuite' => 'logVideoUrl');
  }

  public function logVideoUrl($event)
  {
    // Eg 'user:some-kind-of-guid@ondemand.saucelabs.com/wd/hub'.
      if (!preg_match('/^(.*)@ondemand\.saucelabs\.com/', $this->wd_host, $matches)) {
      // Not a saucelabs wd_host.
      return;
    }

    $credentials = $matches[1];
    // Get the session_id from saucelabs.
    /** @var $driver Selenium2Driver */
    $driver = $this->mink->getSession('selenium2')->getDriver();
    $url = $driver->getWebDriverSession()->getUrl();
    $session_id = substr($url, strpos($url, '/session/') + strlen('/session/'));
    if (!empty($session_id)) {
      $auth = hash_hmac('md5', $session_id, $credentials);

      $video_url = 'https://saucelabs.com/jobs/' . $session_id . '?auth=' . $auth;
      print($video_url . "\n");
    }
  }
}
