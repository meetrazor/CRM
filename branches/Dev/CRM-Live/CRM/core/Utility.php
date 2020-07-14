<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
/**
 *
 * This class is for project dependent functionalities
 * For the new project we can delete the class member and functions
 *
 */
//include_once '../phpexcel/PHPExcel.php';
require(__DIR__ . '/Httpful/Bootstrap.php');
\Httpful\Bootstrap::init();

use \Httpful\Request;


class Utility
{

    public static $pagelist = array(

        // Campaign
        'Transaction List' => 'activity.php',
        'Transaction Add/Update' => 'activity_addedit.php',

        // Campaign
        'Activity List' => 'bd_activity.php',
        'Activity Add/Update' => 'bd_activity_addedit.php',


        // Campaign
        'Campaign List' => 'campaign.php',
        'Campaign Add/Update' => 'campaign_addedit.php',
        'Campaign Type List' => 'campaign_type.php',


        //Customer
        'Customer List' => 'customer.php',
        'Customer Add/Update' => 'customer_addedit.php',

        //General
        'Change Password' => 'change_password.php',
        'Edit Profile' => 'edit_profile.php',

        'Access Forbidden' => 'no_access.php',

        //Customer
        'CW Leads' => 'lead.php',
        'CW Lead Add/Update' => 'lead_addedit.php',

        // Masters
        'Break Type' => 'break_type.php',
        'Break Violation' => 'break_violation.php',
        'Company List' => 'company.php',
        'Country State City' => 'country_state_city.php',
        'Disposition List' => 'disposition.php',
        'Education Master' => 'education.php',
        'Emergency Master' => 'emergency.php',
        'Payment Types' => 'payment_type.php',
        'Status Master' => 'status.php',
        'Add Status' => 'status_add.php',
        'Edit Status' => 'status_edit.php',
        'Sub Locality List' => 'sub_locality.php',
        'Tier List' => 'tier_master.php',
        'Loan/Product List' => 'category.php',
        'Tier Category Rate' => 'tier_category.php',
        'Tier Category Rate Add' => 'tier_category_add.php',
        'Tier Category Rate Edit' => 'tier_category_edit.php',
        'Time Slot List' => 'time_slot.php',
        'Reason List' => 'time_slot.php',
        'Query Stage List' => 'query_stage.php',
        'Sub Query Stage List' => 'sub_query_stage.php',
        'Sub Query Stage Add/Update' => 'sub_query_stage_addedit.php',
        'Reason Type List' => 'reason.php',
        'Question List' => 'question.php',
        'Loan Type List' => 'loan.php',
        'Question Add/Update' => 'question_addedit.php',
        'Query Type List' => 'query_type.php',

        // Partner
        'Partner List' => 'partner.php',
        'Partner Add/Update' => 'partner_addedit.php',
        'Partner Commission' => 'partner_commission.php',
        'Partner Ledger' => 'partner_ledger.php',
        'Partner Payout' => 'partner_payout.php',
        'Withdrawal Request' => 'partner_withdrawal.php',

        // Prospect
        'Prospect List' => 'prospect.php',
        'Prospect Add/Update' => 'prospect_addedit.php',


        // Bank
        'Bank List' => 'bank.php',
        'Bank Add/Update' => 'bank_addedit.php',

        // Role Permission Masters
        'Role And Permissions' => 'role_permission_master.php',
        'Add New Role And Permissions' => 'role_add_permission.php',
        'Edit Exisitng Role\'s Permissions' => 'role_edit_permission.php',


        // Tutorial
        'Tutorial List' => 'tutorial.php',

        // Users
        'Add/Update User' => 'user_add.php',
        'Users List' => 'users.php',

        // Updates
        'Update List' => 'update.php',


        // User Permission Masters
        'User And Permissions' => 'user_permission_master.php',
        'Add User\'s Permissions' => 'user_add_permission.php',
        'Edit Exisitng User\'s Permissions' => 'user_edit_permission.php',

        //Customer
        'Vendor List' => 'vendor.php',
        'Vendor Add/Update' => 'vendor_addedit.php',

        // Rss Feed
        'Rss Feed List' => 'rss_feed.php',
        'Rss Feed Add/Update' => 'rss_feed_addedit.php',

        // reports

        'Agent Wise Report' => 'agent_report.php',
        'BD Wise Report' => 'bd_report.php',
        'city Wise Report' => 'city_report.php',
        'Campaign Wise Report' => 'campaign_report.php',
        'Product Wise Report' => 'product_report.php',
        'Month Wise Report' => 'month_report.php',
        'Reason Wise Report' => 'reason_report.php',
        'Bank Wise Report' => 'bank_report.php',
        'Segment Wise Report' => 'segment_report.php',
        'Priority Wise Report' => 'priority_report.php',
        'Support Agent Wise Report' => 'support_agent_report.php',
        'Escalate To Two Wise Report' => 'escalate_to_2_report.php',
        'Escalate To Three Wise Report' => 'escalate_to_3_report.php',
        'Agent Performance Report' => 'agent_performance.php',
        'Question Error Report' => 'question_error_report.php',
        'Average Response Time Report' => 'avg_resolve_time.php',

        // ticket
        'Ticket List' => 'ticket.php',
        'Ticket History List' => 'ticket_history.php',
        'Ticket View' => 'view_single_ticket.php',
        'Ticket Add/Update' => 'ticket_addedit.php',
        'Ticket Merge' => 'ticket_merge.php',

        // audit
        'Call Audit List' => 'call_audit.php',
        'Call Audit Add/Update' => 'call_audit_addedit.php',

        // activity log
        'Activity Log' => 'activity_log.php',

    );

    public static $pagemenu = array(
        'transaction' => array(
            'activity.php',
            'activity_addedit.php',
        ),

        'activity' => array(
            'bd_activity.php',
            'bd_activity_addedit.php',
        ),

        'campaign' => array(
            'campaign.php',
            'campaign_addedit.php',
        ),

        'customer' => array(
            'customer.php',
            'customer_addedit.php',
        ),

        'lead' => array(
            'lead.php',
            'lead_addedit.php',
        ),

        'master' => array(
            'break_type.php',
            'break_violation.php',
            'category.php',
            'company.php',
            'campaign_type.php',
            'country_state_city.php',
            'disposition.php',
            'education.php',
            'emergency.php',
            'payment_type.php',
            'status.php',
            'status_add.php',
            'status_edit.php',
            'sub_locality.php',
            'tier_master.php',
            'tier_category.php',
            'tier_category_add.php',
            'tier_category_edit.php',
            'time_slot.php',
            'vendor.php',
            'vendor_addedit.php',
            'bank.php',
            'bank_addedit.php',
            'question.php',
            'question_addedit.php',
            'reason.php',
            'sub_query_stage.php',
            'query_stage.php',
            'sub_query_stage_addedit.php',
            'loan.php',
        ),
        'ap_master' => array(
            'category.php',
            'company.php',
            'country_state_city.php',
            'education.php',
            'emergency.php',
            'payment_type.php',
            'status.php',
            'status_add.php',
            'status_edit.php',
            'sub_locality.php',
            'tier_master.php',
            'tier_category.php',
            'tier_category_add.php',
            'tier_category_edit.php',
            'time_slot.php',
        ),
        'tel_master' => array(
            'break_type.php',
            'break_violation.php',
            'campaign_type.php',
            'disposition.php',
            'vendor.php',
            'vendor_addedit.php',
        ),

        'support' => array(
            'reason.php',
            'query_stage.php',
            'sub_query_stage.php',
            'question.php',
            'question_addedit.php',
            'query_type.php',
            'bank.php',
            'bank_addedit.php'
        ),

        'partner' => array(
            'partner.php',
            'partner_addedit.php',
            'partner_commission.php',
            'partner_ledger.php',
            'partner_payout.php',
            'partner_withdrawal.php'

        ),

        'prospect' => array(
            'prospect.php',
            'prospect_addedit.php',
        ),

        'rss' => array(
            'rss_feed.php',
            'rss_feed_addedit.php'
        ),

        'tutorial' => array(
            'tutorial.php',
        ),


        'user' => array(
            'role_permission_master.php',
            'role_add_permission.php',
            'role_edit_permission.php',
            'users.php',
            'user_add.php',
            'user_permission_master.php',
            'user_add_permission.php',
            'user_edit_permission.php',
        ),

        'update' => array(
            'update.php',
        ),

        'REPORT' => array(
            'agent_report.php',
            'bd_report.php',
            'city_report.php',
            'campaign_report.php',
            'product_report.php',
            'month_report.php',
            'reason_report.php',
            'bank_report.php',
            'segment_report.php',
            'priority_report.php',
            'support_agent_report.php',
            'escalate_to_2_report.php',
            'escalate_to_3_report.php',
            'agent_performance.php',
            'question_error_report.php'
        ),

        'CALL AUDIT' => array(
            'call_audit.php',
            'call_audit_addedit.php'
        ),
        'TICKET' => array(
            'ticket.php',
            'ticket_history.php',
            'ticket_addedit.php',
            'ticket_merge.php'
        ),
        'ACTIVITY LOG' => array(
            'activity_log.php',
        ),
    );

    public static function PageDisplayName($page)
    {

        $page_disp_name = array_search($page, self::$pagelist);
        return (false !== $page_disp_name) ? ucwords($page_disp_name) : '';
    }

    public static function ParentMenuActive($section, $page)
    {

        return (array_key_exists($section, self::$pagemenu) && in_array($page, self::$pagemenu[$section]));
    }

    public static function MiddleBreadCrumb($page_title, $page_link)
    {

        global $db;
        $token = isset($_GET['token']) ? $db->FilterParameters($_GET['token']) : "";
        if (parse_url($page_link, PHP_URL_QUERY)) {
            $middle_breadcrumb = "<li><a href='$page_link&token=" . $token . "'>$page_title</a><span class='divider'><i class='icon-angle-right arrow-icon'></i></span></li>";
        } else {
            $middle_breadcrumb = "<li><a href='$page_link?token=" . $token . "'>$page_title</a><span class='divider'><i class='icon-angle-right arrow-icon'></i></span></li>";
        }
        return $middle_breadcrumb;
    }


    public static function addOrFetchFromTable($table, $data, $whereField, $where)
    {
        global $db;
        $count = 0;

        $res = $db->FetchRowWhere($table, array($whereField), $where);
        $count = $db->CountResultRows($res);
        //echo $count."<br/>";
        if ($count > 0) {
            $row = $db->MySqlFetchRow($res);
            $id = $row[$whereField];
        } else {
            $id = $db->Insert($table, $data, true);
        }
        return $id;
    }

    public static function addOrUpdateTable($table, $data, $whereField, $where)
    {
        global $db;

        $res = $db->FetchRowWhere($table, array($whereField), $where);
        $count = $db->CountResultRows($res);

        if ($count > 0) {
            $row = $db->MySqlFetchRow($res);
            $id = $row[$whereField];
            $db->UpdateWhere($table, $data, $where);
        } else {
            $id = $db->Insert($table, $data, true);
        }
        return $id;
    }

    public static function checkOrFetchFromTable($table, $data, $whereField, $where)
    {
        global $db;
        $count = 0;

        $res = $db->FetchRowWhere($table, array($whereField), $where);
        $count = $db->CountResultRows($res);
        //echo $count."<br/>";
        if ($count > 0) {
            $row = $db->MySqlFetchRow($res);
            $id = $row[$whereField];
        } else {
            $id = 0;
        }
        return $id;
    }

    public static function DeleteInquiryDoc($inquiry_id, $file_name)
    {

        $upload_path = '../tender_docs';

        $inquiry_file = realpath($upload_path) . '/' . $inquiry_id . '/' . $file_name;
        $inquiry_file = str_replace('\\', '/', $inquiry_file);

        if (file_exists($inquiry_file)) {
            @unlink($inquiry_file);
        }
    }

    public static function DeleteInquiryDocByInquiryDocId($inquiry_doc_id)
    {

        global $db;
        $inq_doc_res = $db->FetchRow('inquiry_doc', 'inquiry_doc_id', $inquiry_doc_id, array('inquiry_id', 'filename'));
        if ($db->CountResultRows($inq_doc_res) > 0) {

            $inq_doc_row = $db->MySqlFetchRow($inq_doc_res);

            self::DeleteInquiryDoc($inq_doc_row['inquiry_id'], $inq_doc_row['filename']);
        }

        $db->Delete('inquiry_doc', 'inquiry_doc_id', $inquiry_doc_id);
    }

    public static function ShowMessage($title, $message, $class = 'error')
    {

        echo "<div class='alert alert-$class'>" .
            "<button data-dismiss='alert' class='close' type='button'><i class='icon-remove'></i></button>" .
            "<strong><i class='icon-remove'></i> $title </strong>" .
            $message . "<br>" .
            "</div>";
    }

    public static function PrintMessage($title, $message, $class = 'error')
    {

        echo "<div class='alert alert-$class'>" .
            "<button data-dismiss='alert' class='close' type='button'><i class='icon-remove'></i></button>" .
            "<strong><i class='icon-remove'></i>$title</strong> $message<br>" .
            "</div>";
    }

    public static function GenerateNo($table, $type)
    {
        global $db;
        $id = $db->GetNextAutoIncreamentValue($table);
        return Core::PadString($id, 2, Utility::getPrefix($type));
    }

    public static function getPrefix($type)
    {
        $prefix = array(
            "lead" => "CW1212",
            "partner" => "CWAP1212",
            "vendor" => "VEN",
        );

        return (array_key_exists($type, $prefix)) ? $prefix[$type] : "";
    }

    public static function CityStateCountry($city_id)
    {

        global $db;

        $main_table = array('city ct', array('ct.city_id', 'ct.city_name'));
        $join_tables = array(

            array('left', 'state s', '(s.state_id=ct.state_id)', array('s.state_name', 's.state_id')),
            array('left', 'country c', '(c.country_id=ct.country_id)', array('c.country_name', 'c.country_id')),
        );

        $condition = "ct.city_id='$city_id'";

        $res = $db->JoinFetch($main_table, $join_tables, $condition, null, array(0, 1));
        $row = $db->MySqlFetchRow($res);

        return $row;
    }

    public function properImplode($data_array, $extract)
    {

        $proper_implode = array();

        for ($i = 0; $i < count($extract); $i++) {
            $proper_implode[$i] = $data_array[$extract[$i]];
        }

        return implode(',', $proper_implode);
    }


    /**
     * Find inquiry products
     * @param int $inq_id : inquiry id
     * @return associative array in which key is products id and value is products name
     */
    public static function inquiryProducts($inq_id)
    {

        global $db;

        $main_table = array('inquiry_product ip', array());
        $join_tables = array(
            array('left', 'products p', 'ip.product_id=p.product_id', array('product_name'))
        );
        $res = $db->JoinFetch($main_table, $join_tables, "ip.inquiry_id='$inq_id'", array('p.product_name' => 'asc'));
        $rows = $db->FetchToArrayFromResultset($res);

        return $rows;
    }

    public static function PrintIfNotNull($val)
    {

        $null = array('', ' ', '00-00-0000', '01-01-1970', '0000-00-00', '1970-01-01', 'null');

        return (in_array($val, $null)) ? '' : $val;
    }

    public static function keyFilter($key)
    {

        $key = str_replace("_", " ", $key);
        $key = ucwords($key);
        return $key;
    }

    public static function dateFormate($date)
    {
        $originalDate = $date;
        $newDate = date("d-m-y", strtotime($originalDate));
        return $newDate;
    }

    public static function userName($id)
    {
        global $db;
        $user_name = "-";
        $uname = $db->FetchCellValue('user', 'first_name', 'user_id = ' . $id . '');
        if ($uname) {
            $user_name = $uname;
        }
        return $user_name;
    }


    public static function registraionEmailToUser($user_id, $password)
    {
        global $db;
        $cols = array('first_name', 'last_name', 'email', 'pass_str');
        $condition = "user_id=$user_id";
        $answer = $db->Fetch("admin_user", $cols, $condition);
        $result = $db->FetchToArrayFromResultset($answer);
        $body = "Congratulations!!!<br/>You have successfully registered to CRM<br/>" .
            "You can now login to " . SITE_ROOT . " using following credentials<br/><br/>" .
            "First Name :" . $result[0]['first_name'] . "<br />" .
            "Last Name  :" . $result[0]['last_name'] . "<br />" .
            "Email      :" . $result[0]['email'] . "<br />" .
            "Password   :" . $password . "<br />";
        return $body;
    }

    public static function registraionEmailToPartner($partner_id, $password)
    {
        global $db;
        $cols = array('first_name', 'last_name', 'email');
        $condition = "partner_id=$partner_id";
        $answer = $db->Fetch("partner_master", $cols, $condition);
        $result = $db->FetchToArrayFromResultset($answer);
        $body = "Congratulations!!!<br/>You have successfully registered to CRM<br/>" .
            "You can now login to " . SITE_ROOT . " using following credentials<br/><br/>" .
            "First Name :" . $result[0]['first_name'] . "<br />" .
            "Last Name  :" . $result[0]['last_name'] . "<br />" .
            "Email      :" . $result[0]['email'] . "<br />" .
            "Password   :" . $password . "<br />";
        return $body;
    }

    public static function openTicketBody($ticketId, $ticketNo)
    {
        global $db;
        $body = "Dear Sir/Madam,<br/><br/>" .
            "Greetings from PSB Loans in 59 Minutes !!! <br/><br/>" .

            "We have successfully registered your complaint. Your ticket no is <b>" . $ticketNo . "</b>. <br/><br/>" .

            "You will receive one more email after the resolution of your query. <br/><br/>" .

            "Please forward your issues/queries to appropriate channel only, so that each & every issue is being <br/>" .
            "tracked and resolved on time.<br /><br/>" .

            "Please note this email is an automated one. So, please do not give a reply to this email. <br /><br /><br/>" .

            "<b>For Lender (Banker) Journey Issues</b><br />" .
            "banksupport@psbloansin59minutes.com<br/>" .
            "<b>For Borrower (Customer) Journey Issues</b><br />" .
            "support@psbloansin59minutes.com<br/><br />" .
            "Team,<br/>".
            "PSB Loans in 59 Minutes";
        return $body;
    }

    public static function closeTicketBody($ticketId, $ticketNo)
    {
        global $db;
        $body = "Dear Sir/Madam,<br/><br/>" .
            "Greetings from PSB Loans in 59 Minutes !!! <br/><br/>" .

            "Your complaint ticket number <b>" . $ticketNo . "</b> has been resolved now. <br/><br/>" .

            "If your issue is not resolved yet, reply us on the same loop. In case you are facing any other <br/>" .
            "issue/queries then please send us a fresh email. <br/><br/>" .

            "Please forward your issues/queries to appropriate channel only, so that each & every issue is being <br/>" .
            "tracked and resolved on time.<br /><br/>" .

            "Please note this email is an automated one. So, please do not give a reply to this email. <br /><br /><br/>" .

            "<b>For Lender (Banker) Journey Issues</b><br />" .
            "banksupport@psbloansin59minutes.com<br/>" .
            "<b>For Borrower (Customer) Journey Issues</b><br />" .
            "support@psbloansin59minutes.com<br/><br />" .
            "Team,<br/>".
            "PSB Loans in 59 Minutes";
        return $body;
    }

    public static function insertEMailLog($requestTime, $status, $users, $emailType, $content, $contact = '', $contactType = 'email', $type = '', $typeId = '')
    {
        global $db;
        $email_log = array();
        $recipient = is_array($users) ? $users : array("$users");
        if (count($recipient) > 0) {
            foreach ($recipient as $userId) {
                $email_log['request_time'] = $requestTime;
                $email_log['response_time'] = DATE_TIME_DATABASE;
                $email_log['status'] = $status['success'];
                $email_log['response'] = $status['msg'];
                $email_log['user_id'] = $userId;
                $email_log['email_type'] = $emailType;
                $email_log['email_content'] = $content;
                $email_log['contact'] = $contact;
                $email_log['contact_type'] = $contactType;
                $email_log['type'] = $type;
                $email_log['type_id'] = $typeId;
                $db->Insert("log_email", $email_log);
            }
        }
        return true;
    }


    public static function sendSMS($mobileNos, $message)
    {
        $mobileNo = '';
        if (is_array($mobileNos)) {
            $mobileNo = implode(",", $mobileNos);
        } else {
            $mobileNo = $mobileNos;
        }
        $message = str_replace(" ", "%20", $message);

        $smsUrl = "http://api.msg91.com/api/sendhttp.php?country=91&sender=" . SMS_SENDER . "&route=" . SMS_ROUTE . "&authkey=" . SMS_AUTH . "&mobiles=" . $mobileNo . "&message=" . $message . "";

        //echo $smsUrl."<br/>";
        try {
            $resp['msg'] = Request::get($smsUrl)
                ->send();
            $resp['success'] = true;
            return $resp;

        } catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
            $resp['msg'] = "error";
            $resp['success'] = false;
            return $resp;
        }

    }

    public static function UploadPath()
    {

        $dir_path = dirname(__FILE__);

        $dir_path = realpath($dir_path . '/../uploads') . '/';

        return $dir_path;
    }

    public static function docExtensions()
    {

        $extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'swf', 'pdf', 'xls', 'xlsx', 'xml', 'zip', 'doc', 'docx');

        return $extensions;
    }

    public static function audioExtensions()
    {

        $extensions = array('mp3');

        return $extensions;
    }

    public static function imageExtensions()
    {

        $extensions = array('jpg', 'jpeg', 'png', 'gif');

        return $extensions;
    }

    public static function checkPartnerActive($partnerId)
    {

        global $db;

        $condition = "partner_id='{$partnerId}'";
        $res = $db->FetchRowWhere('partner_master', array('partner_id', 'email', 'pass', 'is_active', 'first_name', 'last_name'), $condition);
        $counter = $db->CountResultRows($res);
        if (1 == $counter) {

            $userData = mysql_fetch_assoc($res);
            if ($userData['is_active'] == 1) {
                return 1;
            } else {
                return USER_INACTIVE;
            }
        } else {
            return USER_NOT_FOUND;
        }
    }

    public static function checkLeadAccepted($leadId)
    {

        global $db;

        $condition = " lead_id='{$leadId}' ";
        $res = $db->FetchRowWhere('lead_master', array('lead_id', 'partner_id'), $condition);
        $counter = $db->CountResultRows($res);
        if (1 == $counter) {

            $userData = mysql_fetch_assoc($res);
            if ($userData['partner_id'] == 0) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public static function sendPushNotificationToGCM($registatoin_ids, $message)
    {
        //$this->autoRender = 'false';
        //Google cloud messaging GCM-API url
        $url = 'https://android.googleapis.com/gcm/send';
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );

        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        //        core::PrintArray($result);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    public static function getPartnersForLead($leadId)
    {

        global $db;

        $partnersQuery = "
SELECT distinct(pm.partner_id), pm.gcm_reg_id, pm.device_platform, pm.mobile_no
FROM lead_master lm, partner_master pm, status_master sm, partner_category pc
WHERE sm.is_default = 1 AND sm.status_id = lm.status_id
AND lm.partner_id = 0 
-- AND lm.sub_locality_id = psl.sub_locality_id AND psl.partner_id = pm.partner_id
AND lm.city_id = pm.city_id
AND lm.category_id = pc.category_id AND pc.partner_id = pm.partner_id
-- AND pm.partner_id = 
AND pm.partner_id NOT IN (select lme1.partner_id from lead_master lme1 where lme1.lead_id =(SELECT MAX(lead_id) FROM lead_master lme2 WHERE lme2.lead_id < lm.lead_id) )
AND lm.lead_id = {$leadId}
";
        $partners = $db->FetchToArrayFromResultset($db->Query($partnersQuery));

        if (count($partners) == 0) {
            $partnersQuery = "
SELECT distinct(pm.partner_id), pm.gcm_reg_id, pm.device_platform, pm.mobile_no
FROM lead_master lm, partner_master pm, status_master sm, partner_category pc
WHERE sm.is_default = 1 AND sm.status_id = lm.status_id
AND lm.partner_id = 0 
AND lm.city_id = pm.city_id
AND lm.category_id = pc.category_id AND pc.partner_id = pm.partner_id
-- AND pm.partner_id NOT IN (select lme1.partner_id from lead_master lme1 where lme1.lead_id =(SELECT MAX(lead_id) FROM lead_master lme2 WHERE lme2.lead_id < lm.lead_id) )
AND lm.lead_id = {$leadId}
";
            $partners = $db->FetchToArrayFromResultset($db->Query($partnersQuery));
        }

        if (count($partners) == 0) {
            $partnersQuery = "
SELECT distinct(pm.partner_id), pm.gcm_reg_id, pm.device_platform, pm.mobile_no
FROM lead_master lm, partner_master pm, status_master sm
WHERE sm.is_default = 1 AND sm.status_id = lm.status_id
AND lm.partner_id = 0 
AND lm.city_id = pm.city_id
-- AND lm.category_id = pc.category_id AND pc.partner_id = pm.partner_id
-- AND pm.partner_id NOT IN (select lme1.partner_id from lead_master lme1 where lme1.lead_id =(SELECT MAX(lead_id) FROM lead_master lme2 WHERE lme2.lead_id < lm.lead_id) )
AND lm.lead_id = {$leadId}
";
            $partners = $db->FetchToArrayFromResultset($db->Query($partnersQuery));
        }

        return $partners;
    }

    public static function getPartnerForLead($partnerId)
    {

        global $db;

        $partnersQuery = "
SELECT distinct(pm.partner_id), pm.gcm_reg_id, pm.device_platform,concat(pm.first_name,' ',pm.last_name) as partner_name, pm.mobile_no
FROM partner_master pm
WHERE pm.partner_id = '{$partnerId}'
";
        $partners = $db->FetchToArrayFromResultset($db->Query($partnersQuery));

        return $partners;
    }

    public static function getPartnersForNotification()
    {

        global $db;

        $partnersQuery = "
SELECT pm.partner_id, pm.gcm_reg_id, pm.device_platform
FROM partner_master pm
WHERE pm.is_active = 1
";

        $partners = $db->FetchToArrayFromResultset($db->Query($partnersQuery));

        return $partners;
    }

    public static function sendPushNotificationForLead($leadId, $leadName, $partnerId = 0)
    {

        if ($partnerId == 0) {
            $partners = self::getPartnersForLead($leadId);
        } else {
            $partners = self::getPartnerForLead($partnerId);
        }

//        Core::PrintArray($partners);

        $androidGcmTokens = array();

        $pushMessage = array(
            'title' => "New Lead!",
            "message" => "New lead available - {$leadName}",
            "notification_type" => "2",
            "style" => "inbox",
            "summaryText" => "There are %n% notifications",
            "image" => "www/res/icons/android/hdpi.png",
            "ledColor" => array(0, 0, 129, 111),
            "vibrationPattern" => array(2000, 1000, 500, 500)
        );

        foreach ($partners as $partner) {

            if (strtolower($partner['device_platform']) == "android" && trim($partner['gcm_reg_id']) != "") {
                array_push($androidGcmTokens, $partner['gcm_reg_id']);
            }
        }

        $androidGcmChunk = array_chunk($androidGcmTokens, 1000);

//        Core::PrintArray($androidGcmTokens);

        foreach ($androidGcmChunk as $key => $arr) {
            self::sendPushNotificationToGCM($arr, $pushMessage);
        }

//        self::sendPushNotificationToGCM("e56x2nI37Dk:APA91bGOXT3I1jeTc03nIpk47gO7gWLn5DjOQClBKRnLcFRbEBZyNBba96ioXX1Tmm8M1ieCIJv_PYq0fFURnSxNge4Bxm8m7ebM7zknTyZeKu-IVp-zFCXH3-Vps1cQTO4cd1EeZysC", $pushMessage);

    }


    public static function sendPushNotificationForUpdates($title, $message)
    {

        $partners = self::getPartnersForNotification();

//        Core::PrintArray($partners);

        $androidGcmTokens = array();

        $pushMessage = array(
            'title' => $title,
            "message" => $message,
            "notification_type" => "1",
            "style" => "inbox",
            "summaryText" => "There are %n% notifications",
            "image" => "www/res/icons/android/hdpi.png",
            "ledColor" => array(0, 0, 129, 111),
            "vibrationPattern" => array(2000, 1000, 500, 500)
        );

        foreach ($partners as $partner) {
            if (strtolower($partner['device_platform']) == "android" && trim($partner['gcm_reg_id']) != "") {
//                echo "here";
//                if($partner['gcm_reg_id'] == "fh07k0PXlc8:APA91bFnG7IhZRDDt4hwAIF0_oZHxneGCoJh-8g-GBQiE5MHS7Pv_hogXmqT7Mgp_vegLWk_cXivzGmUBf5go5NWxz0k9F_wAjAfSaz_ebdaZtaWgtDIwTkzJ8utxQvR7_ts-mLRBfPO")
                array_push($androidGcmTokens, $partner['gcm_reg_id']);
            }
        }

        $androidGcmChunk = array_chunk($androidGcmTokens, 1000);

        //Core::PrintArray($androidGcmTokens);

        foreach ($androidGcmChunk as $key => $arr) {
            self::sendPushNotificationToGCM($arr, $pushMessage);
        }

//        self::sendPushNotificationToGCM("e56x2nI37Dk:APA91bGOXT3I1jeTc03nIpk47gO7gWLn5DjOQClBKRnLcFRbEBZyNBba96ioXX1Tmm8M1ieCIJv_PYq0fFURnSxNge4Bxm8m7ebM7zknTyZeKu-IVp-zFCXH3-Vps1cQTO4cd1EeZysC", $pushMessage);

    }

    public static function array_column(array $input, $columnKey, $indexKey = null)
    {
        $array = array();
        foreach ($input as $value) {
            if (!isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }

            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }

        return $array;
    }

    public static function userTypeLabel($label = "user", $type = UT_ADMIN)
    {
        $labels = array(
            UT_ADMIN => array(
                "user" => "Users"
            ),
            UT_TC => array(
                "user" => "Telecallers"
            ),

            UT_KC => array(
                "user" => "Users"
            ),
        );
        $type = (isset($type) && $type != '') ? $type : UT_ADMIN;
        return (array_key_exists($type, $labels)) ? array_key_exists($label, $labels[$type]) ? $labels[$type][$label] : "" : "";
    }

    public static function activityCondition()
    {
        global $ses;
        $condition = "1=1";
        $userType = $ses->Get("user_type");
        $isAdmin = $ses->Get("is_admin");
        $userId = $ses->Get("user_id");
        $childIds = Utility::getUserChild($userId, "user");
        if ($userType == UT_TC && $isAdmin == 1) {
            $condition .= "";
        } elseif ($userType == UT_TC) {
            $condition .= " and am.created_by = {$userId}";
        } elseif ($userType == UT_BD) {
            $condition .= " and am.created_by in ($childIds)";
        } elseif ($userType == UT_KC) {
            $condition .= " and am.created_by in ($childIds)";
        }
        return $condition;
    }


    public static function ticketCondition()
    {
        global $ses;
        $condition = "1=1";
        $userType = $ses->Get("user_type");
        $isAdmin = $ses->Get("is_admin");
        $userId = $ses->Get("user_id");
        $childIds = Utility::getUserChild($userId, "user");
        if ($userType == UT_TC && $isAdmin == 1) {
            $condition .= "";
        } elseif ($userType == UT_TC) {
            // $condition .= " and t.created_by = {$userId}";
        } elseif ($userType == UT_BD) {
            $condition .= " and t.created_by in ($childIds)";
        } elseif ($userType == UT_KC) {
            $condition .= " and t.created_by in ($childIds)";
        } elseif ($userType == UT_ST) {
            $condition .= " and (t.assign_to in ($childIds) or t.escalate_to_2 in ($childIds) or t.escalate_to_3 in ($childIds))";
        }
        return $condition;
    }


//    public static function getNextProspect($userId,$typeId=''){
//        global $db;
//        $typeId = ($typeId != '') ? $typeId : 0;
//        $nextId = '';
//        $userCampaign = $db->FetchToArray("campaign_telecaller","campaign_id","user_id = '{$userId}'");
//        if($typeId == ''){
//            $sql = "SELECT
//                    am.type_id,
//                    d.disposition_id,
//                    TIMESTAMPDIFF(MINUTE,NOW(),am.follow_up_date_time)
//                    FROM activity_master AS am
//                    LEFT JOIN disposition_master AS d
//                    ON d.disposition_id = am.disposition_id
//                    WHERE am.is_latest = 1
//                    AND TIMESTAMPDIFF(MINUTE,NOW(),follow_up_date_time) <= 0
//                    AND am.type_id IN(SELECT
//                                     prospect_id
//                                   FROM prospect_master
//                                   WHERE token = '0') AND d.is_close = 0
//                    ORDER BY TIMESTAMPDIFF(MINUTE,NOW(),am.follow_up_date_time)DESC
//                    LIMIT 0,1";
////            $nextData = $db->FetchRowForForm("activity_master",array("type_id","disposition_id"),"TIMESTAMPDIFF(MINUTE,NOW(),follow_up_date_time) <= 2 and is_latest = 1 and
////             type_id in (select prospect_id from prospect_master where token = '0')
////              order by TIMESTAMPDIFF(MINUTE,NOW(),follow_up_date_time) desc");
//            $sqlR = $db->Query($sql);
//            $nextData = $db->FetchRowInAssocArray($sqlR);
//            $nextId = $nextData['type_id'];
//            if(isset($nextData['disposition_id'])){
//                $statusCheck = $db->FetchCellValue("disposition_master","is_close","disposition_id = '{$nextData['disposition_id']}'");
//                if($statusCheck == 1){
//                    $nextId = '';
//                }
//            }
//        }
//
//        if($nextId == '' and count($userCampaign) > 0) {
//            $userCampaignIds = implode(",",$userCampaign);
//            $nextId = $db->FetchCellValue("prospect_master","prospect_id","is_done = 0 and token = '0' and campaign_id in ($userCampaignIds)",array("created_at"=>"asc"));
//        }
//
//        if(isset($nextId) and $nextId == ''){
//            $nextId = $db->FetchCellValue("prospect_master","prospect_id","is_done = 0 and token = '0'",array("created_at"=>"asc"));
//        }
//        if($nextId != ''){
//            $token = core::GenRandomStr(6);
//            $db->UpdateWhere("prospect_master",array("token"=>$token,"token_updated"=>DATE_TIME_DATABASE),"prospect_id = {$nextId}");
//        }
//        return $nextId;
//    }

    public static function getNextProspect($userId, $typeId = '')
    {
        global $db;
        $typeId = ($typeId != '') ? $typeId : 0;
        $nextId = '';
        $userCampaign = $db->FetchToArray("campaign_telecaller", "campaign_id", "user_id = '{$userId}' and campaign_id in (select campaign_id from campaign_master where is_active = 1)");

        $queueData = array();
        if (count($userCampaign) > 0) {
            if ($nextId == '') {
                $userCampaignIds = implode(",", $userCampaign);
                $queueData = $db->FetchRowForForm("prospect_queue", array("prospect_id", "prospect_queue_id"), "campaign_id in ($userCampaignIds) and is_done = 0 and is_block = 0 and
                (date_queued is not null and date_queued <= '" . date("Y-m-d H:i:s") . "')
                order by date_queued desc");
                if (isset($queueData['prospect_id']) && $queueData['prospect_id'] == '') {
                    $queueData = $db->FetchRowForForm("prospect_queue", array("prospect_id", "prospect_queue_id"), "campaign_id in ($userCampaignIds) and is_done = 0 and is_block = 0 and
                    (date_queued is null) order by date_queued desc");
                }
            }
        }

        if (count($queueData) == 0) {
            $userCampaign = $db->FetchToArray("campaign_telecaller", "DISTINCT(campaign_id)");
            $userCampaignIds = (count($userCampaign) > 0) ? implode(",", $userCampaign) : "-1";
            $queueData = $db->FetchRowForForm("prospect_queue", array("prospect_id", "prospect_queue_id"), "campaign_id not in ($userCampaignIds) and is_done = 0 and is_block = 0 and
                (date_queued is not null and date_queued <= '" . date("Y-m-d H:i:s") . "')
                order by date_queued desc");
            if (isset($queueData['prospect_id']) && $queueData['prospect_id'] == '') {
                $queueData = $db->FetchRowForForm("prospect_queue", array("prospect_id", "prospect_queue_id"), "campaign_id not in ($userCampaignIds) and is_done = 0 and is_block = 0 and
                    (date_queued is null) order by date_queued desc");
            }
        }

        if (count($queueData) > 0 and $queueData['prospect_id'] != '') {
            $db->UpdateWhere("prospect_queue", array("is_block" => 1, "block_timestamp" => date("Y-m-d H:i:s", strtotime("+10 min"))), "prospect_queue_id = {$queueData['prospect_queue_id']}");
        }
        return $queueData;
    }

    public static function checkProspect($prospectId, $token = '')
    {
        global $db;
        $prospectData = $db->FetchRowForForm("prospect_master", "*", "prospect_id = '{$token}'");
        if ($token != '') {
            if ($token == $prospectData['token']) {
                return $prospectData['prospect_id'];
            }
        }
    }

    public static function getUpcomingCall($userId, $typeId = '')
    {
        global $db;
        $typeId = ($typeId != '') ? $typeId : 0;
        //$data = $db->FetchRowForForm("activity_master",array("follow_up_date_time","type_id"),"created_by = '{$userId}' and TIMESTAMPDIFF(MINUTE,NOW(),follow_up_date_time) < 20  and is_latest = 1 order by TIMESTAMPDIFF(MINUTE,NOW(),follow_up_date_time) desc");
        $data = $db->FetchRowForForm("activity_master", array("follow_up_date_time", "type_id"), "TIMESTAMPDIFF(MINUTE,NOW(),follow_up_date_time) < 20  and is_latest = 1 order by TIMESTAMPDIFF(MINUTE,NOW(),follow_up_date_time) desc");
        return $data;
    }

    public static function rssToTime($rss_time)
    {
        $day = substr($rss_time, 5, 2);
        $month = substr($rss_time, 8, 3);
        $month = date('m', strtotime("$month 1 2011"));
        $year = substr($rss_time, 12, 4);
        $hour = substr($rss_time, 17, 2);
        $min = substr($rss_time, 20, 2);
        $second = substr($rss_time, 23, 2);
        $timezone = substr($rss_time, 26);

        $timestamp = mktime($hour, $min, $second, $month, $day, $year);

//        date_default_timezone_set('UTC');
//
//        if(is_numeric($timezone)) {
//            $hours_mod = $mins_mod = 0;
//            $modifier = substr($timezone, 0, 1);
//            $hours_mod = (int) substr($timezone, 1, 2);
//            $mins_mod = (int) substr($timezone, 3, 2);
//            $hour_label = $hours_mod>1 ? 'hours' : 'hour';
//            $strtotimearg = $modifier.$hours_mod.' '.$hour_label;
//            if($mins_mod) {
//                $mins_label = $mins_mod>1 ? 'minutes' : 'minute';
//                $strtotimearg .= ' '.$mins_mod.' '.$mins_label;
//            }
//            $timestamp = strtotime($strtotimearg, $timestamp);
//        }

        return $timestamp;
    }

    public static function checkUrlExists($url)
    {
        if (!$fp = curl_init($url)) return false;
        return true;
    }

    public static function getCutString($string, $cut = "90")
    {
        $string = Core::StripAllSlashes($string);
        $string = strip_tags($string);
        if (strlen($string) > $cut) {
            $stringCut = substr($string, 0, $cut);
            if (strrpos($stringCut, ' ')) {
//                echo strlen($string);
//                echo "</br>";
                //echo substr($string, $cut -1, strlen($string));
                return substr($stringCut, 0, strrpos($stringCut, ' ')) . "<span data-placement='bottom' data-rel='tooltip' data-original-title='" . substr($string, $cut - 1, strlen($string)) . "'>..</span>";
            } else {
                return $stringCut . "<span data-placement='bottom' data-rel='tooltip' data-original-title='" . substr($string, $cut - 1, strlen($string)) . "'>..</span>";
            }
        } else {
            return $string;
        }
    }


    public static function cleanedit($value)
    {
        //$value = strip_tags($value);
        $value = str_ireplace('\r\n', '', $value);
        //   $value = htmlentities($value);
        $value = stripslashes($value);
        return $value;
    }

    public static function createCustomer($prospectId)
    {
        global $db;
        $leadData = $db->FetchRowForForm("prospect_master", "*", "prospect_id = '{$prospectId}'");
        $contacts = $db->FetchToArray("prospect_contact", "*", "prospect_id = '{$prospectId}'");
        $email = array();
        $number = array();
        foreach ($contacts as $contact) {
            if ($contact['contact_type'] == 'phone') {
                $number[] = $contact;
            } else {
                $email[] = $contact;
            }
        }
        $leadMobile = implode(",", $number);
        $leadEmail = implode(",", $email);
        $customerId = Utility::addOrFetchFromTable("customer_master",
            array(
                "mobile_no" => $leadMobile,
                "email" => $leadEmail,
                "customer_name" => $leadData['prospect_name'],
                "customer_from" => "web",
                "pincode" => $leadData['pincode'],
                "state_id" => $leadData['state_id'],
                "city_id" => $leadData['city_id'],
                "address" => $leadData['address']
            ), "customer_id", "mobile_no in ($leadMobile)");
        return $customerId;
    }


    public static function getBdForLead($cityId, $userType = UT_BD)
    {
        global $db;
        $nextBd = '';
        $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
        $joinTable = array(
            array("left", "user_city as uc", "uc.user_id = au.user_id")
        );
        $bdQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.has_token = 1  and au.user_type = " . $userType . " and uc.city_id = '{$cityId}'");
        $bdD = $db->FetchRowInAssocArray($bdQ);
        $lastBdId = $bdD['user_id'];
        //$lastBdId = $db->FetchCellValue("admin_user","user_id","user_type = ".UT_BD." and city_id = '{$cityId}' and has_token = 1");
        if ($lastBdId != '') {
            $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
            $joinTable = array(
                array("left", "user_city as uc", "uc.user_id = au.user_id")
            );
            $bdQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_id > $lastBdId  and au.user_type = " . $userType . " and uc.city_id = '{$cityId}'");
            $bdD = $db->FetchRowInAssocArray($bdQ);
            $nextBd = $bdD['user_id'];
            //$nextBd = $db->FetchCellValue("admin_user","user_id","user_id = (SELECT MIN(user_id) FROM admin_user WHERE user_id > $lastBdId and user_type = ".UT_BD." and city_id = '{$cityId}')");
            if ($nextBd == '') {
                $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
                $joinTable = array(
                    array("left", "user_city as uc", "uc.user_id = au.user_id")
                );
                $bdQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_type = " . $userType . " and uc.city_id = '{$cityId}'");
                $bdD = $db->FetchRowInAssocArray($bdQ);
                $nextBd = $bdD['user_id'];
            }
        } else {
            $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
            $joinTable = array(
                array("left", "user_city as uc", "uc.user_id = au.user_id")
            );
            $bdQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_type = " . $userType . " and uc.city_id = '{$cityId}'");
            $bdD = $db->FetchRowInAssocArray($bdQ);
            $nextBd = $bdD['user_id'];
        }
        if ($nextBd != '') {
            $db->Query(
                "UPDATE admin_user au
LEFT JOIN user_city as uc
    ON uc.user_id = au.user_id
SET au.has_token = 0
WHERE au.is_active = 1 and au.user_type = " . $userType . " and uc.city_id = '{$cityId}'");
            //$db->UpdateWhere("admin_user",array("has_token"=>0),"user_type = ".UT_BD." and city_id = '{$cityId}'");
            $db->UpdateWhere("admin_user", array("has_token" => 1), "user_id = '{$nextBd}'");
        }
        return $nextBd;
    }

    public static function getUserForTicket($reasonId, $userType = UT_ST)
    {
        global $db;
        $nextUser = '';
        $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
        $joinTable = array(
            array("left", "user_reason as rm", "rm.user_id = au.user_id")
        );
        $userQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_level = 'level1' and au.has_token = 1  and au.user_type = " . $userType . " and rm.reason_id = '{$reasonId}'");
        $userD = $db->FetchRowInAssocArray($userQ);
        $lastUserId = $userD['user_id'];
        //$lastUserId = $db->FetchCellValue("admin_user","user_id","user_type = ".UT_BD." and city_id = '{$cityId}' and has_token = 1");
        if ($lastUserId != '') {
            $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
            $joinTable = array(
                array("left", "user_reason as rm", "rm.user_id = au.user_id")
            );
            $userQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_level = 'level1' and au.user_id > $lastUserId  and au.user_type = " . $userType . " and rm.reason_id = '{$reasonId}'");
            $userD = $db->FetchRowInAssocArray($userQ);
            $nextUser = $userD['user_id'];
            //$nextUser = $db->FetchCellValue("admin_user","user_id","user_id = (SELECT MIN(user_id) FROM admin_user WHERE user_id > $lastUserId and user_type = ".UT_BD." and city_id = '{$cityId}')");
            if ($nextUser == '') {
                $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
                $joinTable = array(
                    array("left", "user_reason as rm", "rm.user_id = au.user_id")
                );
                $userQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_level = 'level1' and au.user_type = " . $userType . " and rm.reason_id = '{$reasonId}'");
                $userD = $db->FetchRowInAssocArray($userQ);
                $nextUser = $userD['user_id'];
            }
        } else {
            $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
            $joinTable = array(
                array("left", "user_reason as rm", "rm.user_id = au.user_id")
            );
            $userQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_level = 'level1' and au.user_type = " . $userType . " and rm.reason_id = '{$reasonId}'");
            $userD = $db->FetchRowInAssocArray($userQ);
            $nextUser = $userD['user_id'];
        }
        if ($nextUser != '') {
            $db->Query(
                "UPDATE admin_user au
LEFT JOIN user_reason as rm
    ON rm.user_id = au.user_id
SET au.has_token = 0
WHERE au.is_active = 1 and au.user_level = 'level1' and au.user_type = " . $userType . " and rm.reason_id = '{$reasonId}'");
            //$db->UpdateWhere("admin_user",array("has_token"=>0),"user_type = ".UT_BD." and city_id = '{$cityId}'");
            $db->UpdateWhere("admin_user", array("has_token" => 1), "user_id = '{$nextUser}'");
        }
        return $nextUser;
    }

    public static function getUserForTicket_dep($queryStageId, $userType = UT_ST)
    {
        global $db;
        $nextUser = '';
        $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
        $joinTable = array(
            array("left", "user_query_stage as uqs", "uqs.user_id = au.user_id")
        );
        $userQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.has_token = 1  and au.user_type = " . $userType . " and uqs.query_stage_id = '{$queryStageId}'");
        $userD = $db->FetchRowInAssocArray($userQ);
        $lastUserId = $userD['user_id'];
        //$lastUserId = $db->FetchCellValue("admin_user","user_id","user_type = ".UT_BD." and city_id = '{$cityId}' and has_token = 1");
        if ($lastUserId != '') {
            $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
            $joinTable = array(
                array("left", "user_query_stage as uqs", "uqs.user_id = au.user_id")
            );
            $userQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_id > $lastUserId  and au.user_type = " . $userType . " and uqs.query_stage_id = '{$queryStageId}'");
            $userD = $db->FetchRowInAssocArray($userQ);
            $nextUser = $userD['user_id'];
            //$nextUser = $db->FetchCellValue("admin_user","user_id","user_id = (SELECT MIN(user_id) FROM admin_user WHERE user_id > $lastUserId and user_type = ".UT_BD." and city_id = '{$cityId}')");
            if ($nextUser == '') {
                $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
                $joinTable = array(
                    array("left", "user_query_stage as uqs", "uqs.user_id = au.user_id")
                );
                $userQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_type = " . $userType . " and uqs.query_stage_id = '{$queryStageId}'");
                $userD = $db->FetchRowInAssocArray($userQ);
                $nextUser = $userD['user_id'];
            }
        } else {
            $mainTable = array("admin_user as au", array("min(au.user_id) as user_id"));
            $joinTable = array(
                array("left", "user_query_stage as uqs", "uqs.user_id = au.user_id")
            );
            $userQ = $db->JoinFetch($mainTable, $joinTable, "au.is_active = 1 and au.user_type = " . $userType . " and uqs.query_stage_id = '{$queryStageId}'");
            $userD = $db->FetchRowInAssocArray($userQ);
            $nextUser = $userD['user_id'];
        }
        if ($nextUser != '') {
            $db->Query(
                "UPDATE admin_user au
LEFT JOIN user_query_stage as uqs
    ON uqs.user_id = au.user_id
SET au.has_token = 0
WHERE au.is_active = 1 and au.user_type = " . $userType . " and uqs.query_stage_id = '{$queryStageId}'");
            //$db->UpdateWhere("admin_user",array("has_token"=>0),"user_type = ".UT_BD." and city_id = '{$cityId}'");
            $db->UpdateWhere("admin_user", array("has_token" => 1), "user_id = '{$nextUser}'");
        }
        return $nextUser;
    }

    public static function getReportingUserId($tree, $parent_tree = array(), $type = 'partner')
    {
        global $db;
        global $ses;
        $userType = $ses->Get("user_type");
        $out = array();
        foreach ($tree as $key => $children) {
            $new_tree = $parent_tree;
            $new_tree[] = $children;
            $parent = ($userType == UT_IA) ? $db->FetchToArray("partner_master", "partner_id", "parent_partner_id = '{$children}'") : $db->FetchToArray("admin_user", "user_id", "reporting_to = '{$children}'");
//            core::PrintArray($parent);
            if (count($parent)) {
                $child_trees = self::getReportingUserId($parent, $new_tree, $type);
                foreach ($child_trees as $tree) {
                    $out[] = $tree;
                }
            } else {
                $out[] = $new_tree;
            }
        }
        return $out;
    }

    public static function getUniqueArray($arry)
    {
        $output = array();
        foreach ($arry as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (!in_array($v, $output) && $v != 0) {
                        $output[] = $v;
                    }
                }
            } else {
                $output[] = $value;
            }
        }
        return $output;
    }


    public static function serverSideValidation($value, $type)
    {
        switch ($type) {
            case "number" :
                if (preg_match('/^\d{10}$/', $value)) {
                    return true;
                } else {
                    return "invalid number ex. 9016023824, 222474278";
                }

            case "email" :
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return true;
                } else {
                    return "invalid email ex. name@company.com";
                }
        }

    }

    public static function pauseCall($agent)
    {


        $callURL = API_URL . "?source=" . SOURCE . "&user=" . USER . "&pass=" . VICI_PASSWORD . "&agent_user=" . $agent . "&function=external_pause&value=PAUSE";
        //echo $callURL."<br/>";
        try {
            $resp = Request::get($callURL)
                ->send();

            return $resp;

        } catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
            $resp = "error";
            return $resp;
        }

    }

    public static function dialCall($agent, $number)
    {

        $number = trim($number);
        $callURL = API_URL . "?source=" . SOURCE . "&user=" . USER . "&pass=" . VICI_PASSWORD . "&agent_user=" . $agent . "&function=external_dial&value=0" . $number . "&phone_code=1&search=YES&preview=NO&focus=NO";
        //echo $smsUrl."<br/>";
        try {
            $message = Request::get($callURL)
                ->send();
            $resp['success'] = true;
            return $message;

        } catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
            $resp['msg'] = "error";
            $resp['success'] = false;
            return $resp;
        }

    }

    public static function hangCall($agent)
    {


        $callURL = API_URL . "?source=" . SOURCE . "&user=" . USER . "&pass=" . VICI_PASSWORD . "&agent_user=" . $agent . "&function=external_hangup&value=1";
        //echo $smsUrl."<br/>";
        try {
            $resp['msg'] = Request::get($callURL)
                ->send();
            $resp['success'] = true;
            return $resp;

        } catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
            $resp['msg'] = "error";
            $resp['success'] = false;
            return $resp;
        }

    }

    public static function statusCall($agent, $status, $date = '')
    {
        $date = isset($date) ? str_replace(" ", "+", $date) : "";
        $callURL = API_URL . "?source=" . SOURCE . "&user=" . USER . "&pass=" . VICI_PASSWORD . "&agent_user=" . $agent . "&function=external_status&value=" . $status . "&callback_datetime=" . $date . "";
        //echo $smsUrl."<br/>";
        try {
            $resp['msg'] = Request::get($callURL)
                ->send();
            $resp['success'] = true;
            return $resp;

        } catch (phpmailerException $e) {
//        echo $e->errorMessage(); //Pretty error messages from PHPMailer
            $resp['msg'] = "error";
            $resp['success'] = false;
            return $resp;
        }

    }

    public static function format_phone($phone)
    {
        $phone = str_replace(" ", "", $phone);
        return $phone;
    }

    public static function viciErrorMessage($resp)
    {

        if (preg_match('/SUCCESS/', $resp)) {
            return array(
                "success" => true,
                "message" => self::get_string_between($resp, ":", "-"),
            );
        } elseif (preg_match('/ERROR/', $resp)) {
            return array(
                "success" => false,
                "message" => self::get_string_between($resp, ":", "-"),
            );
        }

    }

    public static function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    // https://planet.mysql.com/entry/?id=27321

    public static function addChild($child, $parent)
    {
        global $db;
        $sql = "INSERT INTO user_path (ancestor, descendant,length)
                SELECT t.ancestor, $child,t.path_level+1 FROM user_path as t
                WHERE t.descendant = '$parent'
                UNION ALL SELECT $child, $child ,0";
        $db->Query($sql);
    }

    public static function getChild($id)
    {
        global $db;
        $id = is_array($id) ? implode(",", $id) : $id;
        $sql = "select au.user_id FROM admin_user as au
                    JOIN user_path up
                    ON (au.user_id = up.descendant)
                    WHERE up.ancestor IN ($id)";
        $res = $db->Query($sql);
        return ($res != '') ? $db->FetchToArrayFromResultset($res) : array();
    }

    public static function getParent($id)
    {
        global $db;
        $sql = "select au.user_id FROM admin_user as au
                    JOIN user_path up
                    ON (au.user_id = up.descendant)
                    WHERE ttp.descendant = $id";
        $res = $db->Query($sql);
        return ($res != '') ? $db->FetchToArrayFromResultset($res) : array();
    }

    public static function moveSubTree($child, $newParent)
    {
        global $db;
        $check = $db->FetchCellValue("user_path", "descendant", "descendant = '$child' or ancestor = '$child'");
        if ($check == '') {
            self::addChild($child, $newParent);
        } else {
            $childsRes = $db->FetchToArrayFromResultset($db->Query("select descendant from user_path as b where b.ancestor = '$child'"));
            $childs = (count($childsRes) > 0) ? implode(",", $childsRes) : "-1";
            $sql = "DELETE  FROM user_path AS a
                WHERE descendant in ($childs)";
            $db->Query($sql);

            $insertInsideNewParent = "INSERT INTO user_path (ancestor, descendant,path_level)
                                    SELECT supertree.ancestor, subtree.descendant,
                                    supertree.length+subtree.length+1
                                    FROM user_path AS supertree
                                    CROSS JOIN user_path AS subtree
                                    WHERE supertree.descendant = $newParent
                                    AND subtree.ancestor = $child";
            return $db->Query($insertInsideNewParent);
        }
    }

    public static function deleteSubTree($parent, $type = null)
    {
        global $db;
        return $db->DeleteWhere("user_path", "descendant IN (SELECT descendant
                FROM user_path
                WHERE ancestor = $parent and `type` = '$type')");
//        $sql = "DELETE FROM user_path
//                WHERE descendant IN (SELECT descendant
//                FROM user_path
//                WHERE ancestor = $parent and `type` = '$type')";
    }

    public static function hasChild($id)
    {
        global $db;
        $child = self::getChild($id);
        return (count($child) > 1) ? true : false;
    }

    public static function deleteLeafNode($leafNode)
    {
        global $db;
        $db->DeleteWhere("user_path", "descendant = '{$leafNode}'");
    }

    public static function getUserChild($userId, $type = 'partner')
    {
        global $ses;
        global $db;
        $res = self::getReportingUserId(array("$userId"), array(), $type);
        $childIds = implode(",", Utility::getUniqueArray($res));
        return $childIds;
    }

    public static function addLeadUser($userId, $leadId, $type = "bd")
    {
        global $db;
        global $ses;
        global $nf;
        $user_id = $ses->Get("user_id");
        $userType = array(
            "bd" => UT_BD,
            "kc" => UT_KC,
        );

        $db->UpdateWhere("lead_users", array("is_latest" => 0), "lead_id = '{$leadId}' and user_type = '{$type}'");
        $insertId = $db->Insert("lead_users", array(
            "lead_id" => $leadId,
            "user_id" => $userId,
            "user_type" => $type,
            "user_type_id" => $userType[$type],
            "is_latest" => "1",
            "created_at" => DATE_TIME_DATABASE,
            "created_by" => $userId
        ), 1);
        if ($userId != $user_id) {
            $nf->saveNotification($leadId, $user_id, $userId, "assign", 1, "lead");
        }
        return $insertId;
    }

    public static function userCount($type, $condition = '')
    {
        global $db;
        $count = 0;
        if ($type == 'prospect') {

            $condition = (isset($condition) && $condition != '') ? $condition : "date_format(created_at,'%Y-%m-%d') = '" . ONLY_DATE_YMD . "'";
            $record = $db->FetchToArray("prospect_master", "prospect_id", $condition);
            $count = count($record);
        }

        if ($type == 'follow_up') {
            $condition = "disposition_id = " . D_FOLLOW_UP . "";
            if ($condition != '') {
                $condition .= " and date_format(created_at,'%Y-%m-%d') = '" . ONLY_DATE_YMD . "'";
            } else {
                $condition = "date_format(created_at,'%Y-%m-%d') = '" . ONLY_DATE_YMD . "'";
            }

            $record = $db->FetchToArray("activity_master", "activity_id", $condition);
            $count = count($record);
        }

        if ($type == 'call') {
            $sqlWhere = "1=1";
            $sqlWhere .= " and source_type = 'prospect'";
            if (isset($condition) && $condition != '') {
                $sqlWhere .= " and " . $condition;
            } else {
                $sqlWhere .= " and date_format(created_at,'%Y-%m-%d') = '" . ONLY_DATE_YMD . "'";
            }
            //  echo $sqlWhere;
            $record = $db->FetchToArray("activity_master", "activity_id", $sqlWhere);
            $count = count($record);
        }
        if ($type == 'balance_prospect') {
            $condition = '';
            $activityProspect = $db->FetchToArray("activity_master", "DISTINCT(type_id)", "source_type = 'prospect'");
            $prospectIds = (count($activityProspect) > 0) ? implode(",", $activityProspect) : "-1";
            $condition = "prospect_id not in ($prospectIds)";
            $record = $db->FetchToArray("prospect_master", "prospect_id", $condition);

            $count = count($record);
        }
        if ($type == 'follow_up_balance') {
            $condition = '';
            $activityProspect = $db->FetchToArray("activity_master", "type_id", "source_type = 'prospect'");
            $prospectIds = (count($activityProspect) > 0) ? implode(",", $activityProspect) : "-1";
            $condition = (isset($condition) && $condition != '') ? $condition : "date_format(date_queued,'%Y-%m-%d') = '" . ONLY_DATE_YMD . "'";
            $condition .= " and prospect_id in ($prospectIds) and is_done = 0";
            $record = $db->FetchToArray("prospect_queue", "prospect_id", $condition);

            $count = count($record);
        }
        if ($type == 'lead_count') {
            $condition = (isset($condition) && $condition != '') ? $condition : "1=1";
            $mainTable = array("lead_master as lm", array("lm.lead_id"));
            $joinTable = array(
                array("left", "city as c", "c.city_id = lm.city_id"),
                array("left", "prospect_master as pm", "pm.prospect_id = lm.prospect_id"),
                array("left", "campaign_master as cm", "cm.campaign_id = pm.campaign_id"),
                array("left", "category_master as catm", "catm.category_id = pm.category_id")
            );
            $record = $db->JoinFetch($mainTable, $joinTable, $condition);
            $count = $db->CountResultRows($record);
        }
        return $count;
    }

    public static function decodeToUTF8($stringQP, $base = 'windows-1252')
    {
        $pairs = array('?x-unknown?' => "?{$base}?");
        $stringQP = strtr($stringQP, $pairs);
        return imap_utf8($stringQP);
    }

    public static function syncEmailCommands()
    {
        if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] == ''){
            $userId = -9;
        } else {
            $userId = $_SESSION['user_id'];
        }
        if (!function_exists('imap_open')) {
            echo "error";
        }
        global $db;
		$db->CharactersetUTF8();
          $hostname = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';
         // $username = 'banksupport@psbloansin59minutes.com';
          $username = 'meet.razor@outlook.com';
           $password ='RaZoRrEx1994';
        //$username = 'shah.naitik.201090@gmail.com';
        //$password = 'cwmknd#$123';
        //$password = 'BSmknd#$7530';
        //$password = 'Destination@1990';
         // $password = 'BSmknd#$7530';
        //$password = 'cwmknd#$123';
        //$password = 'BSmknd#$7530';
        //$password = 'Destination@1990';
        // $password = 'BSmknd#$7530';
        //$password = '201090)()Shahna';

        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to IMAP email: ' . imap_last_error());

        $emails = imap_search($inbox, "UNSEEN");

        //core::PrintArray($emails,1);
        /* If emails are returned, cycle through each... */
        if ($emails) {
            $output = array();

            /* Make the newest emails on top */
            //rsort($emails);

            /* For each email... */
            foreach ($emails as $email_number) {

                $headerInfo = imap_headerinfo($inbox, $email_number);
                //core::PrintArray($headerInfo);
                /* get information specific to this email */
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                //core::PrintArray($overview);
                $structure = imap_fetchstructure($inbox, $email_number);
                //core::PrintArray($structure);
                //                $status = imap_mail_move($inbox, $email_number, "MARKED_SYNCED");
                $hostname = isset($headerInfo->from[0]->host) ? $headerInfo->from[0]->host : "-1";
                $isBankDomainId = $db->FetchCellValue("bank_domain", "bank_id", "domain = '{$hostname}'");
                if ($isBankDomainId) {
                    
                        $isBankActive = $db->FetchCellValue("bank_master", "is_active", "bank_id = '{$isBankDomainId}'");
                        if ($isBankActive) {  
                        /*$part = $structure->parts[1];
                        echo $part->encoding;
                        $message = imap_fetchbody($inbox, $email_number, 2);
                        if($part->encoding == 1) {
                            $message = imap_utf8($message);
                        } else if($part->encoding == 2) {
                            $message = imap_binary($message);
                        } else if($part->encoding == 3) {
                            //$message = preg_replace('~[^a-zA-Z0-9+=/]+~s', '', $message);
                            $message = imap_base64($message);
                        }  else if($part->encoding == 4) {
                            $message = quoted_printable_decode($message);
                        } else {
                            $message = imap_qprint($message);
                        }*/

                        $body = self::get_part($inbox, $overview[0]->uid, "TEXT/HTML");
                        // if HTML body is empty, try getting text body
                        if ($body == "") {
                            $body = self::get_part($inbox, $overview[0]->uid, "TEXT/PLAIN");
                        }
                        //                echo $body;
                        //                    exit;
                        //echo $body;
                        $attachments = array();

                        //                if (!$structure->parts) { // simple
                        //                    self::getpart($inbox,$email_number,$structure,0);  // pass 0 as part-number
                        //                }else {  // multipart: cycle through each part
                        //                    foreach ($structure->parts as $partno0=>$p)
                        //                        core::PrintArray($p);
                        //                        self::getpart($inbox,$email_number,$p,$partno0+1);
                        //                }
                        //                exit;

                        /* if any attachments found... */
                        if (isset($structure->parts) && count($structure->parts)) {
                            for ($i = 0; $i < count($structure->parts); $i++) {
                                $attachments[$i] = array(
                                    'is_attachment' => false,
                                    'filename' => '',
                                    'name' => '',
                                    'attachment' => ''
                                );

                                if ($structure->parts[$i]->ifdparameters) {
                                    foreach ($structure->parts[$i]->dparameters as $object) {
                                        if (strtolower($object->attribute) == 'filename') {
                                            $attachments[$i]['is_attachment'] = true;
                                            $attachments[$i]['filename'] = self::decodeToUTF8($object->value);
                                        }
                                    }
                                }

                                if ($structure->parts[$i]->ifparameters) {
                                    foreach ($structure->parts[$i]->parameters as $object) {
                                        if (strtolower($object->attribute) == 'name') {
                                            $attachments[$i]['is_attachment'] = true;
                                            $attachments[$i]['name'] = self::decodeToUTF8($object->value);
                                        }
                                    }
                                }

                                if ($attachments[$i]['is_attachment']) {
                                    $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);

                                    /* 4 = QUOTED-PRINTABLE encoding */
                                    if ($structure->parts[$i]->encoding == 3) {
                                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                    } /* 3 = BASE64 encoding */
                                    elseif ($structure->parts[$i]->encoding == 4) {
                                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                    }
                                }
                            }
                        }

                        $attachmentDb = array();
                        /* iterate through each attachment and save it */
                        foreach ($attachments as $key => $attachment) {
                            if ($attachment['is_attachment'] == 1) {
                                $filename = $attachment['name'];
                                if (empty($filename)) $filename = $attachment['filename'];

                                if (empty($filename)) $filename = time() . ".dat";

                                /* prefix the email number to the filename in case two emails
                                * have the attachment with the same file name.
                                */
                                $path = TICKET_IMAGE_PATH_ABS . ($overview[0]->uid) . "-+-" . $filename;
                                $attachmentDb[$key]['file_name'] = ($overview[0]->uid) . "-+-" . $filename;
                                $attachmentDb[$key]['real_filename'] = $attachment['name'];
                                //$attachmentDb[] = $path;
                                $fp = fopen($path, "w+");
                                fwrite($fp, $attachment['attachment']);
                                fclose($fp);
                            }

                        }


                        //core::PrintArray($attachmentDb);

                        $fromaddr = $headerInfo->from[0]->mailbox . "@" . $headerInfo->from[0]->host;
                        //echo $fromaddr;

                        
                        $emailSubject = isset($overview[0]->subject) ? $overview[0]->subject : "";
                        if($emailSubject != ''){
                            $emailSubject = trim(ltrim($emailSubject,"Re:"));
                            $emailSubject = trim(ltrim($emailSubject,"Fwd:"));
                            $emailSubject = trim(ltrim($emailSubject,"Re:"));
                            array_push($output,$emailSubject);
                        }
                        $sender = isset($headerInfo->from[0]->mailbox) ? $headerInfo->from[0]->mailbox . '@' . $headerInfo->from[0]->host : "-1";
                        $customerData = array(
                            "email" => $sender,
                            "customer_name" => $headerInfo->from[0]->mailbox,
                        );

                        $customerData = array_merge($customerData, $db->TimeStampAtCreate($userId));
                        $customerId = $db->FetchCellValue("customer_master", 'customer_id', "email = '{$sender}'");
                        $mobile_no = $db->FetchCellValue("customer_master", 'mobile_no', "customer_id = '{$customerId}'");
                        if ($mobile_no != '') {
                            $customerData['mobile_no'] = $mobile_no;
                        }
                        if ($customerId == '') {
                            $customerId = $db->Insert("customer_master", $customerData, 1);
                        }
                        $bankId = $db->FetchCellValue("bank_domain", "bank_id", "domain = '{$hostname}'");
                        $dataInsert = array(
                            'sender' => $headerInfo->from[0]->mailbox . '@' . $headerInfo->from[0]->host,
                            'date' => date("Y-m-d", strtotime($overview[0]->date)),
                            'udate' => $overview[0]->udate,
                            'email_subject' => $overview[0]->subject,
                            'message_uid' => $overview[0]->uid,
                            'message_no' => $overview[0]->msgno,
                            //'comment' => $message,
                            'comment' => $body,
                            'bank_id' => $bankId,
                            'reason_id' => 8,
                            'customer_id' => $customerId,
                            'ticket_from' => "email",
                        );

                        $ticketId = $db->FetchCellValue("tickets", 'ticket_id',"(email_subject = '{$emailSubject}' OR email_subject = '{$overview[0]->subject}') and customer_id = '{$customerId}'");
                        //$ticketId = $db->FetchCellValue("tickets", 'ticket_id',"message_uid = '{$overview[0]->uid}' &&  message_no = '{$overview[0]->msgno}'");
                        if($ticketId == ''){
                            $tableId = $db->GetNextAutoIncreamentValue("tickets");
                            $dataInsert['ticket_number'] = Core::PadString($tableId, 5, "PSB");
                            $dataInsert['is_latest'] = 1;
                            $dataInsert['assign_to'] = 0;
                            $dataInsert['status_id'] = 96;
                            $dataInsert = array_merge($dataInsert, $db->TimeStampAtCreate($userId));
                            $insertId = $db->Insert("tickets", $dataInsert, 1);


                            if ($insertId != '') {
                                array_push($output,$insertId);
                                if (is_array($attachmentDb) && count($attachmentDb) > 0) {
                                    foreach ($attachmentDb as $attachment) {
                                        $ticketFileData = array(
                                            'ticket_id' => $insertId,
                                            'filename' => $attachment['file_name'],
                                            "real_filename" => $attachment['real_filename']);
                                        $ticketFileData = array_merge($ticketFileData, $db->TimeStampAtCreate(-9));
                                        $db->Insert("ticket_documents", $ticketFileData);
                                    }
                                }
                            }

                            if (isset($customerData['email']) && $customerData['email'] != '') {
                                $ticketEmailBody = Utility::openTicketBody($insertId, $dataInsert['ticket_number']);
                                $status = sMail(array($customerData['customer_name'] => $customerData['email']), "PSB Loans in 59 Minutes", "Ticket No " . $dataInsert['ticket_number'] . "", $ticketEmailBody, "PSB Loans in 59 Minutes", "no-reply@psbloansin59minutes.com", $filepath = '');
                                Utility::insertEMailLog(DATE_TIME_DATABASE, $status, $customer_id, NET_TICKET, $ticketEmailBody, $customerData['email']);
                            }
                           if(isset($customerData['mobile_no']) && $customerData['mobile_no'] != ''){
                             $customerMessage = "Dear Client,%0aWe have successfully registered your complaint. Your ticket no is ".$dataInsert['ticket_number'].".%0aYou will receive one more SMS after the resolution of your query.%0aTeam,%0aPSB Loans in 59 Minutes";
                                $status = Utility::sendSMS($customerData['mobile_no'],$customerMessage);
                                Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$customer_id,"",$customerMessage,$customerData['mobile_no'],'sms','customer',$customer_id);
                             }
                        } else {
                            $ticketData = $db->FetchToArray("tickets","*","ticket_id = '{$ticketId}'");
                            $ticketHistoryId = $db->Insert("ticket_history",$ticketData[0],1);

                            if($ticketHistoryId != ''){
                                array_push($output,$ticketHistoryId);
                                $lastTicketDocumentId = $db->FetchToArray("ticket_documents","ticket_document_id",'ticket_id = '.$ticketId.' and (ticket_history_id = "" or ticket_history_id is null) order by ticket_document_id desc');
                                $imageHistoryArray = array("ticket_history_id"=>$ticketHistoryId);
                                $lastTicketDocumentId = is_array($lastTicketDocumentId) ? implode(",",$lastTicketDocumentId) : $lastTicketDocumentId;
                                $db->UpdateWhere("ticket_documents", $imageHistoryArray, "ticket_document_id in ($lastTicketDocumentId)");
                            }
                            $data = array_merge($ticketData[0],$db->TimeStampAtUpdate($userId));
                            $statusData = $db->FetchRowForForm("status_master","*","status_id = '{$data['status_id']}'");

                            // if status close then re-open ticket
                            if(isset($statusData['is_close']) && $statusData['is_close'] == 1){
                                $data['status_id'] = S_OPEN;
                            }
                            $data['email_subject'] = $emailSubject;
                            $data['comment'] = $body;
                            $data['bank_id'] = $bankId;
                            $udpate = $db->Update("tickets", $data, "ticket_id", $ticketId);

                            if(is_array($attachmentDb) && count($attachmentDb) > 0){
                                foreach($attachmentDb as $attachment){
                                    $ticketFileData = array(
                                        'ticket_id' => $ticketId,
                                        'filename' => $attachment['file_name'],
                                        "real_filename" => $attachment['real_filename']);
                                    $ticketFileData = array_merge($ticketFileData,$db->TimeStampAtCreate($userId));
                                    $db->Insert("ticket_documents", $ticketFileData);
                                }
                            }


                            if(isset($statusData['is_close']) && $statusData['is_close'] == 1){
                                if(isset($customerData['email']) && $customerData['email'] != '' && $data['reason_id'] = R_BANK_SUPPORT){
                                    $ticketEmailBody = Utility::openTicketBody($ticketId,$data['ticket_number']);
                                    $status = sMail(array($customerData['customer_name'] => $customerData['email']),"PSB Loans in 59 Minutes", "Ticket No ".$data['ticket_number']."", $ticketEmailBody, "PSB Loans in 59 Minutes", "no-reply@psbloansin59minutes.com", $filepath = '');
                                    Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$ticketId,NET_TICKET,$ticketEmailBody,$customerData['email']);
                                }
                                 if(isset($customerData['mobile_no']) && $customerData['mobile_no'] != ''){
                             $customerMessage = "Dear Client,%0aWe have successfully registered your complaint. Your ticket no is ".$data['ticket_number'].".%0aYou will receive one more SMS after the resolution of your query.%0aTeam,%0aPSB Loans in 59 Minutes";
                                $status = Utility::sendSMS($customerData['mobile_no'],$customerMessage);
                                Utility::insertEMailLog(DATE_TIME_DATABASE,$status,$customerId,"",$customerMessage,$customerData['mobile_no'],'sms','customer',$customerId);
                             }
                            }


                        }
                    }
                }

            }

            imap_close($inbox);
            if (count($output)) {
                return $output;
            }else{
                return false;
            }
            
        } else {
            return false;
        }
    }

    public static function getpart($mbox, $mid, $p, $partno)
    {
        // $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
        global $htmlmsg, $plainmsg, $charset, $attachments;

        // DECODE DATA
        $data = ($partno) ?
            imap_fetchbody($mbox, $mid, $partno) :  // multipart
            imap_body($mbox, $mid);  // simple
        // Any part may be encoded, even plain text messages, so check everything.
        if ($p->encoding == 4)
            $data = quoted_printable_decode($data);
        elseif ($p->encoding == 3)
            $data = base64_decode($data);

        // PARAMETERS
        // get all parameters, like charset, filenames of attachments, etc.
        $params = array();
        if ($p->parameters)
            foreach ($p->parameters as $x)
                $params[strtolower($x->attribute)] = $x->value;
        if ($p->dparameters)
            foreach ($p->dparameters as $x)
                $params[strtolower($x->attribute)] = $x->value;

        // ATTACHMENT
        // Any part with a filename is an attachment,
        // so an attached text file (type 0) is not mistaken as the message.
        if ($params['filename'] || $params['name']) {
            // filename may be given as 'Filename' or 'Name' or both
            $filename = ($params['filename']) ? $params['filename'] : $params['name'];
            // filename may be encoded, so see imap_mime_header_decode()
            $attachments[$filename] = $data;  // this is a problem if two files have same name
        }

        // TEXT
        if ($p->type == 0 && $data) {
            // Messages may be split in different parts because of inline attachments,
            // so append parts together with blank row.
            if (strtolower($p->subtype) == 'plain')
                $plainmsg .= trim($data) . "\n\n";
            else
                $htmlmsg .= $data . "<br><br>";
            $charset = $params['charset'];  // assume all parts are same charset
        }

        // EMBEDDED MESSAGE
        // Many bounce notifications embed the original message as type 2,
        // but AOL uses type 1 (multipart), which is not handled here.
        // There are no PHP functions to parse embedded messages,
        // so this just appends the raw source to the main message.
        elseif ($p->type == 2 && $data) {
            $plainmsg .= $data . "\n\n";
        }
        echo $plainmsg;
        echo $htmlmsg;
        echo $charset;
        core::PrintArray($attachments);
        // SUBPART RECURSION
        if (isset($p->parts)) {
            foreach ($p->parts as $partno0 => $p2)
                getpart($mbox, $mid, $p2, $partno . '.' . ($partno0 + 1));  // 1.2, 1.2.1, etc.
        }
    }


    public static function get_part($imap, $uid, $mimetype, $structure = false, $partNumber = false)
    {
        if (!$structure) {
            $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }
        if ($structure) {
            if ($mimetype == self::get_mime_type($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $text = imap_fetchbody($imap, $uid, $partNumber, FT_UID);
                switch ($structure->encoding) {
                    case 3:
                        return imap_base64($text);
                    case 4:
                        return imap_qprint($text);
                    default:
                        return $text;
                }
            }

            // multipart
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";
                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }
                    $data = self::get_part($imap, $uid, $mimetype, $subStruct, $prefix . ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    public static function get_mime_type($structure)
    {
        $primaryMimetype = ["TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER"];

        if ($structure->subtype) {
            return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }

    public static function getFieldHtml($fieldType, $options = array(), $selectedValue = null)
    {
        $html = '';
        if ($fieldType == 'radio') {
            if (is_array($options) && count($options) > 0) {
                foreach ($options as $optionData) {
                    $questionId = $optionData['question_id'];
                    $optionValue = $optionData['option_value'];
                    $optionValueId = $optionData['option_value_id'];
                    $html .= '<label class="inline">';
                    $html .= '<input required name="option_values[' . $questionId . '][option_value]" type="radio" value="' . $optionValueId . "||" . $questionId . "||" . $optionValue . '"';
                    if ($selectedValue != '' and is_array($selectedValue) && in_array($optionValueId, $selectedValue)) {
                        $html .= 'checked';
                    } elseif ($selectedValue != '' and $selectedValue == $optionValueId) {
                        $html .= 'checked';
                    }
                    $html .= '>';
                    $html .= '<span class="lbl">&nbsp;' . $optionValue . '&nbsp</span>';
                }
                $html .= '<span for="option_values[' . $questionId . '][option_value]" class="help-inline"></span>';
            }
        } elseif ($fieldType == 'checkbox') {
            if (is_array($options) && count($options) > 0) {
                foreach ($options as $optionData) {
                    $questionId = $optionData['question_id'];
                    $optionValue = $optionData['option_value'];
                    $optionValueId = $optionData['option_value_id'];
                    $html .= '<label class="inline"><input required name="option_values[' . $questionId . '][option_value][]" value="' . $optionValueId . "||" . $questionId . "||" . $optionValue . '" type="checkbox"';
                    if ($selectedValue != '' and is_array($selectedValue) && in_array($optionValueId, $selectedValue)) {
                        $html .= 'checked';
                    } elseif ($selectedValue != '' and $selectedValue == $optionValueId) {
                        $html .= 'checked';
                    }
                    $html .= '>';
                    $html .= '<span class="lbl">&nbsp;' . $optionValue . '&nbsp</span></label>';
                }
                $html .= '<span for="option_values[' . $questionId . '][option_value][]" class="help-inline"></span>';
            }
        } elseif ($fieldType == 'text' || $fieldType == 'textarea') {
            if ($fieldType == 'text') {
                foreach ($options as $optionData) {
                    $selected = is_array($selectedValue) ? implode(",", $selectedValue) : $selectedValue;
                    $html .= '<input name="option_values[' . $optionData['question_id'] . '][option_value]" type="text" placeholder="Your text here." value="' . $selected . '" required>';
                    $html .= '<input name="option_values[' . $optionData['question_id'] . '][question_id]" type="hidden" value="' . $optionData['question_id'] . '">';
                }
            } elseif ($fieldType == 'textarea') {
                foreach ($options as $optionData) {
                    $selected = is_array($selectedValue) ? implode(",", $selectedValue) : $selectedValue;
                    $html .= '<textarea name="option_values[' . $optionData['question_id'] . '][option_value]" class="span4" placeholder="Your text here." spellcheck="false" value="' . $selected . '" required>';
                    $html .= $selected.'</textarea>';
                    $html .= '<input name="option_values[' . $optionData['question_id'] . '][question_id]" type="hidden" value="' . $optionData['question_id'] . '">';
                }
            }
        }
        return $html;
    }



    public static function writeExportZipExport($name, $headerArr, $dataRows)
    {

        $objPHPExcel = new PHPExcel();
        // create your zip file

        $objPHPExcel->setActiveSheetIndex(0);
        list($fileName,$file) = explode("_",$name);
        $uploadFolder = self::UploadPath().$fileName."/";
        if(!file_exists($uploadFolder)){
            mkdir($uploadFolder, 0777, true);
        }

        $y = 1;
        for($i=0, $c='A';$i < count($headerArr);$i++){

            $column_name = str_replace("_", " ", $headerArr[$i]);
            $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $column_name);
            $c++;

        }
        $objPHPExcel->getActiveSheet()->getStyle("A$y:$c$y")->getFont()->setBold(true);
        $y++;


        $i=0;
        foreach ($dataRows as $key => $displayData){
            $c='A';
            foreach ($displayData as $val){
                $objPHPExcel->getActiveSheet()->SetCellValue($c . $y , $val);
                $i++;
                $c++;
            }
            $y++;
        }
        $name = $name."_".DATETIMEFORMAT.'.xls';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save($uploadFolder.$name);

        return $file = UPLOADS_PATH_REL.$fileName."/".$name;

    }

    public static function getTimeDifferenceBetweenDate($startDate,$endDate){
        $sDate = new DateTime($startDate);
        $eDate = new DateTime($endDate);

        $interval = date_diff($sDate,$eDate);

       return $interval->format('%hH:%iM:%sS');
    }

    public static function formatTimeFromSecond($t,$f=':') // t = seconds, f = separator
    {
        $t = intval($t);
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$t");
        return $dtF->diff($dtT)->format('%a d '.$f. '%h h '.$f. '%i m '.$f. '%s s');
        //return sprintf("%02ds%s%02dm%s%02ds", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
    }

}
