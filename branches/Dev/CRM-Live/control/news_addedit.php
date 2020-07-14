<?php
include_once 'header.php';
$user_id = $ses->Get("user_id");
$table = "rss_feed";
$table_id = "rss_feed_id";
$action = $db->FilterParameters($_GET['act']);
if('addedit' === $action){

    $data = $db->FilterParameters($_POST, array('short_description','description','title'));
    $data['short_description'] = Utility::cleanedit($data['short_description']);
    $data['description'] = Utility::cleanedit($data['description']);
    $data['is_active'] = (isset($data['is_active'])) ? 1 : 0;
    $data['pub_date'] = (isset($data['pub_date'])) ? Core::DMYToYMD($data['pub_date'],true) : '';
    $tags = (isset($data['tag_ids'])) ? $data['tag_ids'] : '';
    $uploadpath = NEWS_IMAGE_PATH_ABS;

    $validator = array(

        'rules' => array(
            'company_id' => array('required' => true,),
            'title' => array('required' => true),
            'short_description' => array('required' => true),
            'description' => array('required' => true),
            'pub_date' => array('required' => true),
            'category_id' => array('required' => true),
        ),
        'messages' => array(
            'company_id' => array('required' => "Please select company"),
            'title' => array('required' => 'Please enter news title'),
            'short_description' => array('required' => 'Please short description'),
            'description' => array('required' => 'Please Enter description'),
            'pub_date' => array('required' => 'Please select publish Date'),
            'category_id' => array('required' => 'Please select category'),
        )
    );

    $validator_obj = new Validator($validator);

    $errors = $validator_obj->validate($data);

    if(count($errors) > 0){

        echo json_encode(array('success' => false, 'errors' => $errors));
        exit;
    }


    $id = (isset($data['rss_feed_id']) && $data['rss_feed_id'] != '') ? $data['rss_feed_id'] : 0;

    $response = array();
    $exist_condition = "title='{$data['title']}' and category_id = '{$data['category_id']}'";

    if($id > 0){

        $exist_condition .= " && rss_feed_id != '$id'";
        $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));

        if($exist == 0){

            $data = array_merge($data,$db->TimeStampAtUpdate($user_id));
            $udpate = $db->Update($table, $data, $table_id, $id);

            if(!empty($tags) && is_array($tags)){
                $db->DeleteWhere("rss_feed_tags","$table_id = '{$id}'");
                foreach($tags as $tag){
                    $data['rss_feed_id'] = $id;
                    $data['tag_id'] = $tag;
                    $db->Insert("rss_feed_tags",$data);
                }
            }
            if(isset($_FILES['image']) && count($_FILES['image']['tmp_name'])){
                if (!file_exists($uploadpath)) {
                    mkdir($uploadpath, 0777, true);
                }
                $cnt = count($_FILES['image']['tmp_name']);
                for($i=0 ; $i<$cnt ; $i++){

                    $filename = Core::UniqueFileName();

                    $extensions = Utility::imageExtensions();

                    $upload_status = $core->UploadMultipleFile('image', MAX_UPLOAD_SIZE, $uploadpath,$i, $extensions, $filename);
                    if($upload_status['status']){
                        $uploadDetails = array('rss_feed_id'=>$id,'filename'=>$upload_status['filename'],"real_filename"=>$upload_status['file']);
                        $db->Insert("rss_feed_image", $uploadDetails);
                    }
                }
            }

            $response['success'] = true;
            $response['act'] = 'updated';
            $response['title'] = 'Successful';
            $response['msg'] = 'Record updated successfully!!';
        }else{

            $response['success'] = false;
            $response['title'] = 'Exist';
            $response['msg'] = "Rss Feed with the title: {$data['title']} already exist";
        }
    }else{

        // Adding a new record if user id is not found
        $exist = $db->FunctionFetch($table, 'count', array($table_id), $exist_condition, array(0,1));
        if($exist == 0){
            if(isset($data['parent_rss_feed_id']) && $data['parent_rss_feed_id'] != '') {
                $db->UpdateWhere($table,array("is_changed"=>1),"rss_feed_id == '{$data['parent_rss_feed_id']}'");
                $data['is_direct'] = 0;
            }
            $news_data = array_merge($data, $db->TimeStampAtCreate($user_id));
            $insertId = $db->Insert($table, $news_data, 1);
            if($insertId != ''){
                if(!empty($tags) && is_array($tags)){
                    foreach($tags as $tag){
                        $data['rss_feed_id'] = $insertId;
                        $data['tag_id'] = $tag;
                        $db->Insert("rss_feed_tags",$data);
                    }
                }
            }

            if(isset($_FILES['image']) && count($_FILES['image']['tmp_name'])){                
                if (!file_exists($uploadpath)) {
                    mkdir($uploadpath, 0777, true);
                }
                $cnt = count($_FILES['image']['tmp_name']);
                for($i=0 ; $i<$cnt ; $i++){

                    $filename = Core::UniqueFileName();

                    $extensions = Utility::imageExtensions();

                    $upload_status = $core->UploadMultipleFile('image', MAX_UPLOAD_SIZE, $uploadpath,$i, $extensions, $filename);
                    if($upload_status['status']){
                        $uploadDetails = array('rss_feed_id'=>$insertId,'filename'=>$upload_status['filename'],"real_filename"=>$upload_status['file']);
                        $db->Insert("rss_feed_image", $uploadDetails);
                    }
                }
            }
            $response['success'] = true;
            $response['act'] = 'added';
            $response['title'] = 'Successful';
            $response['msg'] = 'Record added successfully!!';
        }else{

            $response['success'] = false;
            $response['title'] = 'Exist';
            $response['msg'] = "Rss Feed with the title: {$data['title']} already exist";
        }
    }
    echo json_encode($response);

}

include_once 'footer.php';
