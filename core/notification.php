<?php
/**
 *
 * This class is notification
 * For the new project we can customize our object and object type
 * @author Naitik S. Shah
 * @link shah.naitik.23825@gmail.com
 *
 */
class Notifications
{
    public $table = "notifications";

    /**
     * This function save the notification in database
     * If is useful where you need to show amount in words format    *
     * @param $object_id
     * @param $subject_id
     * @param $actor_id
     * @param $action
     * @param $type_id
     * @param $object_type
     */

    public function saveNotification($object_id, $subject_id, $actor_id,$action,$type_id,$object_type)
    {
        global $db;
        $data['actor_id'] = $actor_id;
        $data['subject_id'] = $subject_id;
        $data['object_id'] = $object_id;
        $data['object_type'] = $object_type;
        $data['type_id'] = $type_id;
        $data['action'] = $action;
        $data['status'] = "unseen";
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $db->Insert($this->table,$data);
    }

    /**
     * @param $actorId
     * @param int $offset
     * @return array
     */

    public function getNotifications($actorId, $offset = 0)
    {
        global $db;
        if($actorId != '') {
            $main_table = array("notifications as nf",array("nf.*"));
            $join_table = array(
                array("INNER","user as actor","nf.actor_id = actor.user_id",array("CONCAT(actor.first_name,' ',actor.last_name) AS actor_name")),
                array("INNER","user as subject","nf.subject_id = SUBJECT.user_id",array("CONCAT(subject.first_name,' ',subject.last_name) AS subject_name")));
            $res = $db->JoinFetch($main_table, $join_table,"actor_id = '{$actorId}'",array("created_at"=>"DESC"),"LIMIT $offset, 5");
            $result = $db->FetchToArrayFromResultset($res);
            $rows = array();
            foreach($result as $row) {
                $row['object'] = $this->getObjectRow($row['type_id'], $row['object_id'],$row['object_type']);
                $rows[] = $row;
            }
            $notifications = array();

            foreach ($rows as $row) {
                $notification = array(
                    'message' => $this->getNotificationMessage($row),
                    'actor_id' => $row['actor_id'],
                    'subject_id' => $row['subject_id'],
                    'object' => $row['object_id'],
                    'notification_id' => $row['notification_id'],
                    'created_at' => $row['created_at'],
                    'action' => $row['action']
                );
                $notifications[] = $notification;
            }

            return $notifications;
        } else {
            return array();
        }
    }

    /**
     * @param $typeId
     * @param $objectId
     * @param $objectType
     * @return array
     */

    protected function getObjectRow($typeId, $objectId,$objectType)
    {
        global $db;
        switch ($objectType) {
            case "lead" :
                return $db->FetchRowInAssocArray($db->FetchRowWhere("lead_master","lead_name as name","lead_id = $objectId"));
                break;
            case "target":
                return $db->FetchRowInAssocArray($db->FetchRowWhere("targets","target_name as name","target_id = $objectId"));
                break;
            case "activity":
                return $db->FetchRowInAssocArray($db->FetchRowWhere("lead_communication","meeting_name as activity_name","lead_communication_id = $objectId"));
                break;
            case "document":
                return $db->FetchRowInAssocArray($db->FetchRowWhere("documents","document_name as name","document_id = $objectId"));
                break;
            case "attendance":
                return $db->FetchRowInAssocArray($db->FetchRowWhere("user_regularization","*","user_regularization_id = $objectId"));
                break;

        }
    }

    /**
     * @param $row
     * @return string
     */

    protected function getNotificationMessage($row)
    {
        global $ses;
        $etoken = $ses->Get("session_id");
        $userName = ($row['actor_id'] == $ses->Get("user_id")) ? "you" : $row['actor_name'];
        $row['subject_name'] = ($row['subject_id'] == $ses->Get("user_id")) ? "you" : $row['subject_name'];
        switch ($row['object_type']) {
            case "lead" :
                return "{$row['subject_name']} {$row['action']} lead <a href='lead.php?lead_id=".$row['object_id']."&etoken=".$etoken."'>{$row['object']['name']}</a> to {$userName}";
                break;
            case "target" :
                return "<a href='user_profile.php?id=".$row['subject_id']."&etoken=".$etoken."'>{$row['subject_name']}</a> {$row['action']} target <a href='target.php?etoken=".$etoken."'>{$row['object']['name']}</a> to {$userName}";
                break;
            case "document" :
                return "<a href='user_profile.php?id=".$row['subject_id']."&etoken=".$etoken."'>{$row['subject_name']}</a> {$row['action']} document <a href='share_document.php?type=myinvitation&etoken=".$etoken."'>{$row['object']['name']}</a> to {$userName}";
                break;
            case "attendance" :
                return "<a href='user_profile.php?id=".$row['subject_id']."&etoken=".$etoken."'>{$row['subject_name']}</a> {$row['action']} attendance regularization <a href='regularization_detail.php?user_regularization_id={$row['object_id']}&etoken=".$etoken."'>from {$row['object']['from_date']} to {$row['object']['to_date']}</a> to {$userName}";
                break;
            case "activity" :
                return "<a href='user_profile.php?id=".$row['subject_id']."&etoken=".$etoken."'>{$row['subject_name']}</a> {$row['action']} activity <a href='activity_view.php?id={$row['object_id']}&etoken=".$etoken."'>{$row['object']['activity_name']}</a> to {$userName}";
                break;
        }
    }

    /**
     * @param $actorId
     * @return array|bool
     */

    public function markSubjectNotificationsSeen($actorId,$notificationId = '')
    {
        global $db;
        global $ses;
        $user_id = $ses->Get("user_id");
        $result = $db->FetchToArray($this->table,"*","actor_id = $actorId");
        $rows = array();
        if(!isset($notificationId) && $notificationId != ''){
            foreach($result as $row){
                $result = $db->UpdateWhere($this->table,array("status"=>"seen","is_display"=>1),"notification_id = {$row['notification_id']}");
            }
        } else {
            $result = $db->UpdateWhere($this->table,array("status"=>"seen","is_display"=>1),"actor_id = {$user_id}");
        }
        return $result;
    }

    /**
     * @param $actorId
     * @return int|string
     */

    public function getUnseenNotificationsCount($actorId){
        global $db;
        if(isset($actorId)){
            $result = $db->FetchToArray($this->table,"*","actor_id = $actorId and status = 'unseen'");
            return count($result);
        } else{
            return '';
        }
    }


    public function getUnDisplayNotifications($actorId, $offset = 0)
    {
        global $db;
        $response = array();
        if($actorId != '') {
            $main_table = array("notifications as nf",array("nf.*"));
            $join_table = array(
                array("INNER","admin_user as actor","nf.actor_id = actor.user_id",array("CONCAT(actor.first_name,' ',actor.last_name) AS actor_name")),
                array("INNER","admin_user as subject","nf.subject_id = SUBJECT.user_id",array("CONCAT(subject.first_name,' ',subject.last_name) AS subject_name")));
            $res = $db->JoinFetch($main_table, $join_table,"actor_id = '{$actorId}' and is_display = 0",array("created_at"=>"DESC"),"LIMIT $offset, 1");
            $result = $db->FetchToArrayFromResultset($res);
            $rows = array();
            foreach($result as $row) {
                $row['object'] = $this->getObjectRow($row['type_id'], $row['object_id'],$row['object_type']);
                $rows[] = $row;
            }
            $notifications = array();

            foreach ($rows as $row) {
                $response = array(
                    'message' => $this->getNotificationMessage($row),
                    'notification_id' => $row['notification_id'],
                );
            }

            return $response;
        } else {
            return array();
        }
    }
}
