<?php
include_once 'core/session.php';
$ses = new Session();
$ses->init();

include_once 'core/Dbconfig.php';

// Creating Db Object and Opening Connection
include_once 'core/Db.php';
include_once 'core/sMail.php';
$db = new Db();
$db->ConnectionOpen();

// Creating Core Object
include_once 'core/Core.php';
$core = new Core();

include_once 'core/SiteSettings.php';

// Creating Utility Object
include_once 'core/Utility.php';
$utl = new Utility();




// Creating Permission Object
include_once 'core/Permission.php';
$acl = new Permission();
$login_id = $ses->Get('user_id');

$token = isset($_GET['token']) ? $db->FilterParameters($_GET['token']) : "";

//if ($token == '') {
//    $response['success'] = false;
//    $response['title'] = "Something going wrong";
//    $response['msg'] = 'Something going wrong, please try after some time';
//    echo json_encode($response);
//    exit;
//}

$current_page = Core::CurrentPage();
//if(!in_array($current_page, $acl->AllowedPages()) && !$acl->IsAllowedPage($login_id, $current_page)){
//    $response['success'] = false;
//    $response['title'] = "Access Denied";
//    $response['msg'] = "You don't have permission to this action!";
//    echo json_encode($response);
//    exit;
//}


$mainTable = array("company_category as cc",array("cc.*"));
$joinTable = array(
    array("left","company_master as cm","cm.company_id = cc.company_id",array("cm.company_name")),
    array("left","category_master as catm","catm.category_id = cc.category_id",array("catm.category_name")),
);
$comCatFeedQ = $db->JoinFetch($mainTable,$joinTable,"cm.is_active = 1");
$comCatFeedData = $db->FetchToArrayFromResultset($comCatFeedQ);

//$content = file_get_contents("http://www.thehindubusinessline.com/economy/?service=rss");
//
//$x = new SimpleXmlElement($content);
//foreach($x->channel->item as $entry) {
//    echo $entry->title."<br>";
//    echo $entry->description."<br>";
//}
//exit;

if(count($comCatFeedData) > 0){
    foreach($comCatFeedData as $feedData){
        $feed = $feedData['rss_feed_link'];

        if(Utility::checkUrlExists($feed)) {
            $content = file_get_contents($feed);

            $x = new SimpleXmlElement($content);

//            echo $feedData['company_name']."</br>";
//            echo $feedData['category_name']."</br>";
//            echo "<ul>";
            foreach($x->channel->item as $entry) {

                $title = $entry->title;
                $title = mysql_real_escape_string(trim($title));
                $pubDateDatabase = date("Y-m-d H:i:s",Utility::rssToTime(trim($entry->pubDate)));
                //$description = $entry->description;
                $description = mb_convert_encoding($entry->description, "HTML-ENTITIES", 'UTF-8');;
                //$description = mysql_real_escape_string(trim($entry->description));
                $guid = $entry->guid;
                $pubDate  =$entry->pubDate;
                $link =  $entry->link;
                $data = array(
                    "title" => $title,
                    "description" => $description,
                    "link" => $link,
                    "pub_date_org" => $pubDate,
                    "pub_date" => $pubDateDatabase,
                    "quid" => $guid,
                    "category_id" => $feedData['category_id'],
                    "company_id" => $feedData['company_id']
                );
                $data = array_merge($data,$db->TimeStampAtCreate($login_id));
                if($guid != ''){
                    $condition = "quid = '{$guid}'";
                } else {
                    $condition = "pub_date = '{$pubDateDatabase}' and company_id = '{$feedData['company_id']}' and category_id = '{$feedData['category_id']}'";
                }
                Utility::addOrUpdateTable("rss_feed",$data,"rss_feed_id",$condition);
            }
            //echo "</ul>";
        }
    }
}
$db->ConnectionClose();
$response['success'] = true;
$response['title'] = "Records Syn";
$response['msg'] = ' Record (s) Synchronize successfully';
echo json_encode($response);





