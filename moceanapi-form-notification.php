<?php

namespace Grav\Plugin;

require __DIR__ . '/vendor/autoload.php';

use Grav\Common\Config\Config;
use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Uri;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\ClientException;

/**
 * Class MoceanApiFormNotifyPlugin
 *
 * @package Grav\Plugin
 */
class MoceanApiFormNotificationPlugin extends Plugin
{
    private $client;

    /**
     * Constructor.
     *
     * @param string $name
     * @param Grav   $grav
     * @param Config $config
     */
    public function __construct($name, Grav $grav, Config $config = null)
    {
        $stack = \GuzzleHttp\HandlerStack::create();
        $this->client = new Client([
            'base_uri' => 'https://rest.moceanapi.com',
            'timeout'  => 10,
            'handler'  => $stack,
        ]);

        parent::__construct($name, $grav, $config);
    }

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin() OR ! $this->isFormTriggered()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onFormProcessed' => ['onFormProcessed', 0],
        ]);
    }

    public function onFormProcessed()
    {
        $referer = $this->getReferrer($this->grav['uri']);

        $enable_notification = $this->config->get('plugins.moceanapi-form-notification.enable_notification');
        $api_key            = $this->config->get('plugins.moceanapi-form-notification.api_key');
        $api_secret             = $this->config->get('plugins.moceanapi-form-notification.api_secret');
        $from                = $this->config->get('plugins.moceanapi-form-notification.from');
        $to                  = $this->config->get('plugins.moceanapi-form-notification.to');
        $message                = $this->config->get('plugins.moceanapi-form-notification.message');
        $medium              = $this->config->get('plugins.moceanapi-form-notification.mocean-medium');

        $enable_auto_response = $this->config->get('plugins.moceanapi-form-notification.enable_auto_response');
        $phone_field          = $this->config->get('plugins.moceanapi-form-notification.phone_field');
        $auto_response_message    = $this->config->get('plugins.moceanapi-form-notification.auto_response_msg');

        $messagesPayload = [];

        $formName = $this->getFormName();

        // Replace placeholders.
        $message              = trim(str_replace('{{FORM_NAME}}', $formName, $message));
        $auto_response_message = trim(str_replace('{{FORM_NAME}}', $formName, $auto_response_message));

        // Phone field value.
        $phone = (isset($_POST['data'][$phone_field]) AND ! empty($_POST['data'][$phone_field])) ? $_POST['data'][$phone_field] : '';

        // Prepare message payload.
        if ($enable_notification AND $to AND $message) {

            $messagesPayload[] = [
                'mocean-api-key' => $api_key,
                'mocean-api-secret' => $api_secret,
                'mocean-to' => $to,
                'mocean-from' => $from,
                'mocean-text' => $message,
                'mocean-resp-format' => 'json',
                'mocean-medium' => 'getgrav',
            ];

        }

        if ($enable_auto_response AND $phone AND $auto_response_message) {

            $messagesPayload[] = [
                'mocean-api-key' => $api_key,
                'mocean-api-secret' => $api_secret,
                'mocean-to' => $phone,
                'mocean-from' => $from,
                'mocean-text' => $auto_response_message,
                'mocean-resp-format' => 'json',
                'mocean-medium' => 'getgrav',
            ];
        }

        // Send SMS notification message.
        try {
            foreach ($messagesPayload as $messagePayload)
            {
                $response = $this->client->post('rest/2/sms', array('form_params'=>$messagePayload));
            }
            
            $result = \GuzzleHttp\json_decode($response->getBody());
            
            if ($response->getStatusCode() == 200) {

                return $this->grav->redirect($referer.'?'.http_build_query(['submitted' => 'sent']));

            }

            return $this->grav->redirect($referer.'?'.http_build_query(['submitted' => 'not_sent', 'error' => $result->messages[0]->status]));

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // your code when the server answers with 5xx
            $result = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            //$result->messages[0]->status
            //$result->messages[0]->err_msg
        } catch (ClientException $e) {
            $result = \GuzzleHttp\json_decode($e->getResponse()->getBody());
            return $this->grav->redirect($referer.'?'.http_build_query(['submitted' => 'not_sent', 'error' => $result->messages[0]->status]));

        }

    }

    /**
     * Check if this is triggered by a form submit.
     *
     * @return bool
     */
    private function isFormTriggered()
    {
        return isset($_POST['__form-name__']) ? true : false;
    }

    /**
     * Get form name.
     *
     * @return string
     */
    private function getFormName()
    {
        return isset($_POST['__form-name__']) ? trim(strip_tags($_POST['__form-name__'])) : '';
    }

    /**
     * Get base referrer.
     *
     * @param Uri $uri
     *
     * @return array|string
     */
    private function getReferrer(Uri $uri)
    {
        $referer = $uri->referrer('/', null);
        $referer = explode('?', $referer);
        $referer = isset($referer[0]) ? trim($referer[0]) : '/';

        return $referer;
    }
}
