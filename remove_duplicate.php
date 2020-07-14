<?php
include_once 'header.php';

$duplicateContact = $db->FetchToArray("prospect_contact","contact","contact_type = 'phone' group by contact having count(*) > 1");
$mainTable = array("prospect_master as pm",array("pc.contact"));
$joinTable = array(
    array("left","prospect_contact as pc","pc.prospect_id = pm.prospect_id",array("pm.first_name","pm.campaign_id"))
);
$duplicateContactR = $db->JoinFetch($mainTable,$joinTable,"pc.contact_type = 'phone'",null,null,"pc.contact,pm.first_name,pm.campaign_id HAVING COUNT(*) > 1");
$duplicateContact = $db->FetchToArrayFromResultset($duplicateContactR);
//core::PrintArray($duplicateContact);
//exit;
if(count($duplicateContact) > 0){
    $i = 0;
    foreach($duplicateContact as $contact){
        $mainTable = array("prospect_master as pm",array("pm.prospect_id"));
        $joinTable = array(
            array("left","prospect_contact as pc","pc.prospect_id = pm.prospect_id")
        );
        $contact['first_name'] = $db->FilterParameters($contact['first_name']);
        $contactProspectR = $db->JoinFetch($mainTable,$joinTable,"pc.contact_type = 'phone' and contact = '{$contact['contact']}'
         and pm.first_name = '{$contact['first_name']}' and pm.campaign_id = '{$contact['campaign_id']}'");
        $contactProspect = $db->FetchToArrayFromResultset($contactProspectR);
        //core::PrintArray($contactProspect);
        //exit;

        //$contactProspect = $db->FetchToArray("prospect_contact","prospect_id","contact_type = 'phone' and contact = '{$contact}'");
        if(count($contactProspect) > 0){

            foreach($contactProspect as $prospectId){
                $prospectQueue = $db->FetchToArray("prospect_queue","*","prospect_id = '{$prospectId}'");
                if(count($prospectQueue) == 1 && $prospectQueue[0]['is_done'] == 0){
                    echo $i;
                    core::PrintArray($prospectQueue);
                    $i++;
                }

            }
        }
    }
}
//core::PrintArray($duplicateContact);
include_once 'footer.php';