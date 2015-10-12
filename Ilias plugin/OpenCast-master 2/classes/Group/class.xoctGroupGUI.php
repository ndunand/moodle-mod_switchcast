<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/classes/class.xoctGUI.php');
require_once('class.xoctGroup.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/classes/class.xoctWaiterGUI.php');
require_once('class.xoctGroupParticipantGUI.php');

/**
 * Class xoctGroupGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy xoctGroupGUI: ilObjOpenCastGUI
 */
class xoctGroupGUI extends xoctGUI {

    /**
     * @param xoctOpenCast $xoctOpenCast
     */
    public function __construct(xoctOpenCast $xoctOpenCast = null) {
        parent::__construct();
        if ($xoctOpenCast instanceof xoctOpenCast) {
            $this->xoctOpenCast = $xoctOpenCast;
        }
        else {
            $this->xoctOpenCast = new xoctOpenCast ();
        }
        $this->tabs->setTabActive(ilObjOpenCastGUI::TAB_GROUPS);
        //		xoctGroup::installDB();
        xoctWaiterGUI::init();
        $this->tpl->addJavaScript($this->pl->getStyleSheetLocation('default/groups.js'));
    }

    protected function index() {
        $temp = $this->pl->getTemplate('default/tpl.groups.html', false, false);
        $temp->setVariable('HEADER_GROUPS', $this->pl->txt('groups_header'));
        $temp->setVariable('HEADER_PARTICIPANTS', $this->pl->txt('groups_participants_header'));
        $temp->setVariable('HEADER_PARTICIPANTS_AVAILABLE', $this->pl->txt('groups_available_participants_header'));
        $temp->setVariable('L_GROUP_NAME', $this->pl->txt('groups_new'));
        $temp->setVariable('PH_GROUP_NAME', $this->pl->txt('groups_new_placeholder'));
        $temp->setVariable('L_FILTER', $this->pl->txt('groups_participants_filter'));
        $temp->setVariable('PH_FILTER', $this->pl->txt('groups_participants_filter_placeholder'));
        $temp->setVariable('BUTTON_GROUP_NAME', $this->pl->txt('groups_new_button'));
        $temp->setVariable('BASE_URL', ($this->ctrl->getLinkTarget($this, '', '', true)));
        $temp->setVariable('GP_BASE_URL',
                ($this->ctrl->getLinkTarget(new xoctGroupParticipantGUI($this->xoctOpenCast), '', '', true)));
        $temp->setVariable('GROUP_LANGUAGE', json_encode([
                'no_title'       => $this->pl->txt('group_alert_no_title'),
                'delete_group'   => $this->pl->txt('group_alert_delete_group'),
                'none_available' => $this->pl->txt('group_none_available')
        ]));
        $temp->setVariable('PARTICIPANTS_LANGUAGE', json_encode([
                'delete_participant' => $this->pl->txt('group_delete_participant'),
                'select_group'       => $this->pl->txt('group_select_group'),
                'none_available'     => $this->pl->txt('group_none_available'),
                'none_available_all' => $this->pl->txt('group_none_available_all'),

        ]));

        $this->tpl->setContent($temp->get());
    }

    /**
     * @param $data
     */
    protected function outJson($data) {
        header('Content-type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function add() {
        // TODO: Implement add() method.
    }

    public function getAll() {
        $arr = [];
        foreach (xoctGroup::getAllForObjId($this->xoctOpenCast->getObjId()) as $group) {
            $stdClass = $group->__asStdClass();
            $stdClass->user_count = xoctGroupParticipant::where(['group_id' => $group->getId()])->count();
            $arr[] = $stdClass;
        }
        $this->outJson($arr);
    }

    protected function create() {
        $obj = new xoctGroup();
        $obj->setSerieId($this->xoctOpenCast->getObjId());
        $obj->setTitle($_POST['title']);
        $obj->create();
        $this->outJson($obj->__asStdClass());
    }

    protected function edit() {
        // TODO: Implement edit() method.
    }

    protected function update() {
        // TODO: Implement update() method.
    }

    protected function confirmDelete() {
        // TODO: Implement confirmDelete() method.
    }

    protected function delete() {
        /**
         * @var $xoctGroup xoctGroup
         */
        $status = false;
        $xoctGroup = xoctGroup::find($_GET['id']);
        if ($xoctGroup->getSerieId() == $this->xoctOpenCast->getObjId()) {
            $xoctGroup->delete();
            $status = true;
        }
        $this->outJson($status);
    }
}