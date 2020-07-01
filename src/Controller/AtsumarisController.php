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

use App\Model\Entity\Atsumari;
use App\Model\Table\AtsumarisTable;
use App\Model\Table\UsersTable;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use JsonSchema\Exception\ResourceNotFoundException;

/**
 * @property AtsumarisTable $Atsumaris
 */
class AtsumarisController extends AppController
{
    /** @var UsersTable */
    public $Users;

    public function initialize(): void
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->Users = TableRegistry::getTableLocator()->get('Users');

    }

    public function index()
    {
        $atsumaris = $this->Atsumaris->find()->orderDesc('Atsumaris.date')->all();
        $this->set(compact("atsumaris"));
    }

    public function view($id)
    {
        if (!isset($this->current_user)) {
            $this->Flash->error('ログインしてください');
            return $this->redirect("/");
        }

        /** @var Atsumari $atsumari */
        $atsumari = $this->Atsumaris->get($id);

        if ($this->current_user->team_id !== $atsumari->team_id) {
            throw new NotFoundException();
        }

        $creator = $this->Users->getByUId($atsumari->user_id);
        $members = [];
        $this->set(compact('atsumari', 'creator', 'members'));
    }

    public function edit($id) {
        if (!isset($this->current_user)) {
            $this->Flash->error('ログインしてください');
            return $this->redirect("/");
        }

        /** @var Atsumari $atsumari */
        $atsumari = $this->Atsumaris->get($id);
        $creator = $this->Users->getByUId($atsumari->user_id);

        if ($this->current_user->id !== $creator->id) {
            throw new NotFoundException();
        }
        $this->set(compact('atsumari'));

        if ($this->request->is('post')) {
            $data =  $this->request->getData();
            $data['start_time'] .= ":00";
            $data['end_time'] .= ":00";
            $this->Atsumaris->patchEntity($atsumari, $data);
            $this->Atsumaris->save($atsumari);
            $this->redirect("/atsumaris/view/".$atsumari->id);
        }
    }
}