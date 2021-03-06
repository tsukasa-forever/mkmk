<?php


namespace App\Model\Domain\Slack\Client;



use App\Model\Domain\Slack\InteractiveMessage\Dialog;
use App\Model\Entity\SlackUser;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Log\Log;

define('BOT_TOKEN', env('BOT_TOKEN'));
define('BOT_CLIENT_ID', env('BOT_CLIENT_ID'));
define('BOT_CLIENT_SECRET', env('BOT_CLIENT_SECRET'));
define('AUTH_REDIRECT_URI', env('DOMAIN'). "/users/login");

class SlackClient
{
    private const OPEN_DIALOG_URL = "https://slack.com/api/dialog.open";

    private const BOT_TOKEN = BOT_TOKEN;

    private const BOT_CLIENT_ID = BOT_CLIENT_ID;

    private const BOT_CLIENT_SECRET = BOT_CLIENT_SECRET;

    private const AUTH_REDIRECT_URI = AUTH_REDIRECT_URI;

    /** @var Client */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }


    /**
     * @param string $trigger_id
     * @param Dialog $dialog
     * @return Response
     */
    public function openDialog(string $trigger_id, Dialog $dialog)
    {
        $res = $this->client->post(self::OPEN_DIALOG_URL, [
            'token' => self::BOT_TOKEN,
            'trigger_id' => $trigger_id,
            'dialog' => json_encode($dialog->toArray())
        ]);
        return $res;
    }

    public function getAuthUrl(string $scope)
    {
        return sprintf("https://slack.com/oauth/v2/authorize?scope=%s&client_id=%s&redirect_uri=%s", $scope, self::BOT_CLIENT_ID, self::AUTH_REDIRECT_URI);
    }

    private function getAuthData(string $code)
    {
        $res = $this->client->post("https://slack.com/api/oauth.v2.access", [
            'code' => $code,
            'client_id' => self::BOT_CLIENT_ID,
            'client_secret' => self::BOT_CLIENT_SECRET,
            'redirect_uri' => self::AUTH_REDIRECT_URI
        ]);
        return $res;
    }

    /**
     * @param string $code
     * @return SlackUser
     */
    public function getAuthedUser(string $code)
    {
        $data = $this->getAuthData($code)->getJson();
        Log::error(json_encode($data));
        $token = $data['access_token'];
        $user_id = $data['authed_user']['id'];
        $data = $this->client->post("https://slack.com/api/users.info", [
            'token' => $token,
            'user' => $user_id,
        ])->getJson();
        return new SlackUser($data['user']);
    }

    /**
     * @param string $user_id
     * @param string $message
     * @return Response
     */
    public function sendDM(string $user_id, string $message)
    {
        $res = $this->client->post("https://slack.com/api/chat.postMessage", [
            'token' => self::BOT_TOKEN,
            'channel' => $user_id,
            'text' => $message
        ]);
        return $res;
    }
}
