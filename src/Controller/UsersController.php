<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Model\Domain\Slack\Client\SlackClient;
use App\Model\Table\UsersTable;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * @property UsersTable $Users
 */
class UsersController extends AppController
{
    /** @var SlackClient */
    private $slack_client;

    public function initialize(): void
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->slack_client = new SlackClient();
        $this->Users = TableRegistry::getTableLocator()->get('Users');
    }

    public function login()
    {
        if (isset($this->current_user)) {
            return $this->redirect("/");
        }

        $code = $this->request->getQuery('code');

        if (empty($code)) {
            return $this->redirect($this->slack_client->getAuthUrl("users:read"));
        }

        $slack_user = $this->slack_client->getAuthedUser($code);

        $user = $this->Users->getByUId($slack_user->id);
        if (!isset($user)) {
            $user = $this->Users->newEntity([
                'u_id' => $slack_user->id,
                'team_id' => $slack_user->team_id,
                'name' => $slack_user->real_name,
                'image_url' => $slack_user->image_url
            ]);
            $this->Users->save($user);
        }

        $this->Session->write('user_id', $user->u_id);
        return $this->redirect('/');
    }


    public function logout()
    {
        $this->Session->write('user_id', null);
        return $this->redirect("/users/login");
    }
}
