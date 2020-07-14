<ul class="nav nav-list">

    <?php
    if ((($acl->IsAllowed($login_id, 'MASTERS', 'Category', 'View Category'))) ||
        (($acl->IsAllowed($login_id, 'MASTERS', 'Country State City', 'View Country State City'))) ||
        (($acl->IsAllowed($login_id, 'MASTERS', 'Education', 'View Education'))) ||
        (($acl->IsAllowed($login_id, 'MASTERS', 'Emergency', 'View Emergency'))) ||
        (($acl->IsAllowed($login_id, 'MASTERS', 'Payment Type', 'View Payment Type'))) ||
        (($acl->IsAllowed($login_id, 'MASTERS', 'Status', 'View Status'))) ||
        (($acl->IsAllowed($login_id, 'MASTERS', 'Sub Locality', 'View Sub Locality'))) ||
        (($acl->IsAllowed($login_id, 'MASTERS', 'Tier', 'View Tier'))) ||
        (($acl->IsAllowed($login_id, 'MASTERS', 'Tier Category Rate', 'View Tier Category Rate')) ||
            (($acl->IsAllowed($login_id, 'MASTERS', 'Time Slot', 'View Time Slot')))
        )
    ) {
        ?>
        <li<?php echo (Utility::ParentMenuActive('master', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-table"></i>
                <span class="menu-text">Master</span>
                <b class="arrow icon-angle-down"></b>
            </a>

            <ul class="submenu">
                <li <?php echo (Utility::ParentMenuActive('tel_masater', $current_page)) ? ' class="active open"' : ''; ?>>
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <i class="icon-table"></i>
                        <span class="menu-text">Tel. Masters</span>
                        <b class="arrow icon-angle-down"></b>
                    </a>
                    <ul class="submenu">
                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Break Type', 'View Break Type'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('break_type.php'))) ? ' class="active"' : ''; ?>><a
                                        href="break_type.php?token=<?php echo $token; ?>"><span class="menu-text">Break Type</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Break Violation', 'View Break Violation'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('break_violation.php'))) ? ' class="active"' : ''; ?>>
                                <a href="break_violation.php?token=<?php echo $token; ?>"><span class="menu-text">Break Violation</span></a>
                            </li>
                        <?php } ?>
                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Campaign Type', 'View Campaign type'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('campaign_type.php'))) ? ' class="active"' : ''; ?>>
                                <a href="campaign_type.php?token=<?php echo $token; ?>"><span class="menu-text">Campaign Type</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Disposition', 'View Disposition'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('disposition.php'))) ? ' class="active"' : ''; ?>>
                                <a href="disposition.php?token=<?php echo $token; ?>"><span class="menu-text">Disposition</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Vendor', 'View Vendor'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('vendor.php', 'vendor_addedit.php'))) ? ' class="active"' : ''; ?>>
                                <a href="vendor.php?token=<?php echo $token; ?>"><span
                                            class="menu-text">Vendor</span></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <li <?php echo (Utility::ParentMenuActive('ap_master', $current_page)) ? ' class="active open"' : ''; ?>>
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <i class="icon-table"></i>
                        <span class="menu-text">AP Masters</span>
                        <b class="arrow icon-angle-down"></b>
                    </a>
                    <ul class="submenu">
                        <?php
                        /*
                        if(($acl->IsAllowed($login_id,'MASTERS', 'Company', 'View Company')))   {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('company.php'))) ? ' class="active"' : '';?>><a href="company.php?token=<?php echo $token; ?>"><span class="menu-text">News Rss</span></a></li>
                        <?php } */ ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Loan Type', 'View Loan Type'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('loan.php'))) ? ' class="active"' : ''; ?>><a
                                        href="loan.php?token=<?php echo $token; ?>"><span class="menu-text">Loan Type</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Category', 'View Category'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('category.php'))) ? ' class="active"' : ''; ?>><a
                                        href="category.php?token=<?php echo $token; ?>"><span class="menu-text">Loan/Product Type</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Country State City', 'View Country State City'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('country_state_city.php'))) ? ' class="active"' : ''; ?>>
                                <a href="country_state_city.php?token=<?php echo $token; ?>"><span class="menu-text">Country State City</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Education', 'View Education'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('education.php'))) ? ' class="active"' : ''; ?>><a
                                        href="education.php?token=<?php echo $token; ?>"><span class="menu-text">Education</span></a>
                            </li>
                        <?php } ?>
                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Emergency', 'View Emergency'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('emergency.php'))) ? ' class="active"' : ''; ?>><a
                                        href="emergency.php?token=<?php echo $token; ?>"><span class="menu-text">Emergency</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Payment Type', 'View Payment Type'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('payment_type.php'))) ? ' class="active"' : ''; ?>>
                                <a href="payment_type.php?token=<?php echo $token; ?>"><span class="menu-text">Payment Type</span></a>
                            </li>
                        <?php } ?>
                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Status', 'View Status'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('status.php', 'status_add.php', 'status_edit.php'))) ? ' class="active"' : ''; ?>>
                                <a href="status.php?token=<?php echo $token; ?>"><span
                                            class="menu-text">Status</span></a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Sub Locality', 'View Sub Locality'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('sub_locality.php'))) ? ' class="active"' : ''; ?>>
                                <a href="sub_locality.php?token=<?php echo $token; ?>"><span class="menu-text">Sub Locality</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Tier', 'View Tier'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('tier_master.php'))) ? ' class="active"' : ''; ?>>
                                <a href="tier_master.php?token=<?php echo $token; ?>"><span
                                            class="menu-text">Tier</span></a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Tier Category Rate', 'View Tier Category Rate'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('tier_category.php', 'tier_category_add.php', 'tier_category_edit.php'))) ? ' class="active"' : ''; ?>>
                                <a href="tier_category.php?token=<?php echo $token; ?>"><span class="menu-text">Tier Category Rate</span></a>
                            </li>
                        <?php } ?>
                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Time Slot', 'View Time Slot'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('time_slot.php'))) ? ' class="active"' : ''; ?>><a
                                        href="time_slot.php?token=<?php echo $token; ?>"><span class="menu-text">Time Slot</span></a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>

                <li <?php echo (Utility::ParentMenuActive('ap_master', $current_page)) ? ' class="active open"' : ''; ?>>
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <i class="icon-table"></i>
                        <span class="menu-text">Support Master</span>
                        <b class="arrow icon-angle-down"></b>
                    </a>
                    <ul class="submenu">
                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Reason', 'View Reason'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('reason.php'))) ? ' class="active"' : ''; ?>><a
                                        href="reason.php?token=<?php echo $token; ?>"><span class="menu-text">Reason Type</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Query Stage', 'View Query Stage'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('query_stage.php'))) ? ' class="active"' : ''; ?>>
                                <a href="query_stage.php?token=<?php echo $token; ?>"><span class="menu-text">Query Stage</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Question', 'View Question'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('question.php','question_addedit.php'))) ? ' class="active"' : ''; ?>><a
                                        href="question.php?token=<?php echo $token; ?>"><span
                                            class="menu-text">Question</span></a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Sub Query Stage', 'View Sub Query Stage'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('sub_query_stage.php','sub_query_stage_addedit.php'))) ? ' class="active"' : ''; ?>>
                                <a href="sub_query_stage.php?token=<?php echo $token; ?>"><span class="menu-text">Sub Query Stage</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Query Type', 'View Query Type'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('query_type.php'))) ? ' class="active"' : ''; ?>><a
                                        href="query_type.php?token=<?php echo $token; ?>"><span class="menu-text">Query Type</span></a>
                            </li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Bank', 'View Bank'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('bank.php', 'bank_addedit.php'))) ? ' class="active"' : ''; ?>>
                                <a href="bank.php?token=<?php echo $token; ?>"><span class="menu-text">Bank</span></a>
                            </li>
                        <?php } ?>

                        
                        <?php
                        if (($acl->IsAllowed($login_id, 'MASTERS', 'Agent Call Type', 'View Agent Call Type'))) {
                            ?>
                        <?php } ?>


                    </ul>
                </li>
                <li <?php echo (Core::isActiveLink(array('agent_call_type.php', 'agent_call_type_addedit.php'))) ? ' class="active"' : ''; ?>>
                            <a href="agent_call_type.php?token=<?php echo $token; ?>"><span class="menu-text">Agent Call Type</span></a>
                        </li>
            </ul>
        </li>
    <?php } ?>

    <?php
    /*
    if(($acl->IsAllowed($login_id,'Rss Feed ', 'Rss Feed', 'View Rss Feed')))   {
        ?>

        <li<?php echo (Utility::ParentMenuActive('rss', $current_page)) ? ' class="active open"' : '';?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-globe"></i>
                <span class="menu-text">Rss Feed</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if(($acl->IsAllowed($login_id,'Rss Feed', 'Rss Feed', 'View Rss Feed')))   {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('rss_feed.php'))) ? ' class="active"' : '';?>><a href="rss_feed.php?token=<?php echo $token; ?>"><span class="menu-text">Rss Feed</span></a></li>
                <?php } ?>

            </ul>
        </li>

    <?php } */ ?>


    <?php
    if ((($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'View Partner'))) ||
        (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'Add Partner'))) ||
        (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'View Partner Commission'))) ||
        (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'View Partner Payout'))) ||
        (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'View Partner Ledger')))
    ) {
        ?>
        <li<?php echo (Utility::ParentMenuActive('partner', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-inr"></i>
                <span class="menu-text">Partner
                <span class="hide badge badge-primary partner-count"><?php echo $partnerCount; ?></span>
            </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">

                <?php
                if (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'View Partner'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('partner.php'))) ? ' class="active"' : ''; ?>><a
                                href="partner.php?token=<?php echo $token; ?>">
                    <span class="menu-text">Partner
                        <span class="hide badge badge-primary partner-count"><?php echo $partnerCount; ?></span>
                    </span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'Add Partner'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('partner_addedit.php'))) ? ' class="active"' : ''; ?>><a
                                href="partner_addedit.php?token=<?php echo $token; ?>"><span class="menu-text">Add Partner</span></a>
                    </li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'View Partner Commission'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('partner_commission.php'))) ? ' class="active"' : ''; ?>><a
                                href="partner_commission.php?token=<?php echo $token; ?>"><span class="menu-text">Commission</span></a>
                    </li>
                <?php } ?>


                <?php
                if (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'View Partner Withdrawal'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('partner_withdrawal.php'))) ? ' class="active"' : ''; ?>><a
                                href="partner_withdrawal.php?token=<?php echo $token; ?>"><span class="menu-text">Withdrawals</span></a>
                    </li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'PARTNER', 'Payout', 'View Partner Payout'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('partner_payout.php'))) ? ' class="active"' : ''; ?>><a
                                href="partner_payout.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">Payout</span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'PARTNER', 'Partner', 'View Partner Ledger'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('partner_ledger.php'))) ? ' class="active"' : ''; ?>><a
                                href="partner_ledger.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">Ledger</span></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'Customer', 'Customer', 'View Customer')) ||
        (($acl->IsAllowed($login_id, 'Customer', 'Customer', 'Add Customer')))
    ) {
        ?>

        <li<?php echo (Utility::ParentMenuActive('customer', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-group"></i>
                <span class="menu-text">Customer
                <span class="hide badge badge-primary customer-count"><?php echo $customerCount; ?></span>
            </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'Customer', 'Customer', 'View Customer'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('customer.php'))) ? ' class="active"' : ''; ?>><a
                                href="customer.php?token=<?php echo $token; ?>">
                    <span class="menu-text">Customer
                        <span class="hide badge badge-primary customer-count"><?php echo $customerCount; ?></span>
                    </span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'Customer', 'Customer', 'Add Customer'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('customer_addedit.php'))) ? ' class="active"' : ''; ?>><a
                                href="customer_addedit.php?token=<?php echo $token; ?>"><span class="menu-text">Add Customer</span></a>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'CAMPAIGN', 'Campaign', 'View Campaign')) ||
        (($acl->IsAllowed($login_id, 'Campaign', 'Campaign', 'Add Campaign')))
    ) {
        ?>

        <li<?php echo (Utility::ParentMenuActive('campaign', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-globe"></i>
                <span class="menu-text">Campaign</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'Campaign', 'Campaign', 'View Campaign'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('campaign.php'))) ? ' class="active"' : ''; ?>><a
                                href="campaign.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">View Campaign</span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'Campaign', 'Campaign', 'Add Campaign'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('campaign_addedit.php'))) ? ' class="active"' : ''; ?>><a
                                href="campaign_addedit.php?token=<?php echo $token; ?>"><span class="menu-text">Add Campaign</span></a>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'PROSPECT', 'Prospect', 'View Prospect')) ||
        (($acl->IsAllowed($login_id, 'Prospect', 'Prospect', 'Add Prospect')))
    ) {
        ?>

        <li<?php echo (Utility::ParentMenuActive('prospect', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-male"></i>
                <span class="menu-text">Prospect</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'Prospect', 'Prospect', 'View Prospect'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('prospect.php'))) ? ' class="active"' : ''; ?>><a
                                href="prospect.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">View Prospect</span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'Prospect', 'Prospect', 'Add Prospect'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('prospect_addedit.php'))) ? ' class="active"' : ''; ?>><a
                                href="prospect_addedit.php?token=<?php echo $token; ?>"><span class="menu-text">Add Prospect</span></a>
                    </li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'transaction', 'transaction', 'View transaction')) ||
        (($acl->IsAllowed($login_id, 'transaction', 'transaction', 'Add transaction')))
    ) {
        ?>

        <li<?php echo (Utility::ParentMenuActive('transaction', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-phone"></i>
                <span class="menu-text">Transactions</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'transaction', 'transaction', 'View transaction'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('activity.php'))) ? ' class="active"' : ''; ?>><a
                                href="activity.php?token=<?php echo $token; ?>"><span class="menu-text">View Transactions</span></a>
                    </li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'transaction', 'transaction', 'Add transaction'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('activity_addedit.php'))) ? ' class="active"' : ''; ?>><a
                                href="activity_addedit.php?type=call&token=<?php echo $token; ?>"><span
                                    class="menu-text">Add Call</span></a></li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'View ticket')) ||
        (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'Add ticket')))
    ) {
        ?>

        <li<?php echo (Utility::ParentMenuActive('TICKET', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-ticket"></i>
                <span class="menu-text">Tickets</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'View ticket'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('ticket.php'))) ? ' class="active"' : ''; ?>><a
                                href="ticket.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">View All Tickets</span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'View ticket'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('ticket.php'))) ? ' class="active"' : ''; ?>><a
                                href="ticket.php?token=<?php echo $token; ?>&status=open"><span class="menu-text">View Open Tickets</span></a>
                    </li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'View ticket'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('ticket.php'))) ? ' class="active"' : ''; ?>><a
                                href="ticket.php?token=<?php echo $token; ?>&status=close"><span class="menu-text">View Closed Tickets</span></a>
                    </li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'View ticket'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('ticket.php'))) ? ' class="active"' : ''; ?>><a
                                href="ticket.php?token=<?php echo $token; ?>&status=unassign"><span class="menu-text">Unassigned Tickets</span></a>
                    </li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'View ticket'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('ticket.php'))) ? ' class="active"' : ''; ?>><a
                                href="ticket.php?show_merged=true&token=<?php echo $token; ?>"><span
                                    class="menu-text">View Merged Tickets</span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'View ticket'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('ticket_history.php'))) ? ' class="active"' : ''; ?>><a
                                href="ticket_history.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">View Tickets History</span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'TICKET', 'ticket', 'Add ticket'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('ticket_addedit.php'))) ? ' class="active"' : ''; ?>><a
                                href="ticket_addedit.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">Add Ticket</span></a></li>
                <?php } ?>

            </ul>
        </li>

    <?php } ?>


    <?php
    if (($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'View Call Audit')) ||
        (($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Add Call Audit')))
    ) {
        ?>

        <li<?php echo (Utility::ParentMenuActive('CALL AUDIT', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-microphone"></i>
                <span class="menu-text">Call Audit</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'View Call Audit'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('call_audit.php'))) ? ' class="active"' : ''; ?>><a
                                href="call_audit.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">View All Call Audits</span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'CALL AUDIT', 'Call Audit', 'Add Call Audit'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('call_audit_addedit.php'))) ? ' class="active"' : ''; ?>><a
                                href="call_audit_addedit.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">Add Call Audit</span></a></li>
                <?php } ?>

            </ul>
        </li>
    <?php } ?>

    <?php
    if ($acl->IsAllowed($login_id, 'Lead', 'Lead', 'View Lead') ||
        (($acl->IsAllowed($login_id, 'Lead', 'Lead', 'Add Lead')))
    ) {
        ?>
        <li<?php echo (Utility::ParentMenuActive('lead', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-book"></i>
                <span class="menu-text">Lead
                <span class="hide badge badge-primary lead-count"><?php echo $leadCount; ?></span>
            </span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'Lead', 'Lead', 'View Lead'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('lead.php', 'lead_addedit.php'))) ? ' class="active"' : ''; ?>>
                        <a href="lead.php?token=<?php echo $token; ?>">
                    <span class="menu-text">Lead
                        <span class="hide badge badge-primary lead-count"><?php echo $leadCount; ?></span>
                    </span></a></li>
                <?php } ?>

                <?php
                /*
                if(($acl->IsAllowed($login_id,'Lead', 'Lead', 'Add Lead')))   {
                ?>
                <li <?php echo (Core::isActiveLink(array('lead_addedit.php'))) ? ' class="active"' : '';?>><a href="lead_addedit.php?token=<?php echo $token; ?>"><span class="menu-text">Add Lead</span></a></li>
                <?php } */ ?>
            </ul>
        </li>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'Activity', 'Activity', 'View Activity')) ||
        (($acl->IsAllowed($login_id, 'Activity', 'Activity', 'Add Activity')))
    ) {
        ?>


        <li<?php echo (Utility::ParentMenuActive('activity', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-comments-alt"></i>
                <span class="menu-text">Activity</span>
                <b
                        class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'Activity', 'Activity', 'View Activity'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('bd_activity.php'))) ? ' class="active"' : ''; ?>><a
                                href="bd_activity.php?token=<?php echo $token; ?>"><span
                                    class="menu-text">View Activity</span></a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'Activity', 'Activity', 'Add Activity'))) {
                    ?>
                    <?php foreach ($activityType as $type => $value) { ?>
                        <li>
                            <a href="bd_activity_addedit.php?type_id=<?php echo $value; ?>&token=<?php echo $token; ?>">Add <?php echo ucwords($value); ?></a>
                        </li>
                    <?php } ?>
                    <?php /*
            <li <?php echo (Core::isActiveLink(array('bd_activity_addedit.php'))) ? ' class="active"' : '';?>><a href="bd_activity_addedit.php?type=call&token=<?php echo $token; ?>"><span class="menu-text">Add Activity</span></a></li>
            */ ?>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>



    <?php
    if ($acl->IsAllowed($login_id, 'UPDATES', 'update', 'View update')
    ) {
        ?>
        <li<?php echo (Utility::ParentMenuActive('update', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-bullhorn"></i>
                <span class="menu-text">Updates</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li <?php echo (Core::isActiveLink(array('update.php'))) ? ' class="active"' : ''; ?>><a
                            href="update.php?token=<?php echo $token; ?>"><span class="menu-text">Updates</span></a>
                </li>
            </ul>
        </li>
    <?php } ?>


    <?php
    if ($acl->IsAllowed($login_id, 'TUTORIAL', 'tutorial', 'View tutorial')
    ) {
        ?>
        <li<?php echo (Utility::ParentMenuActive('tutorial', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-facetime-video"></i>
                <span class="menu-text">Tutorial</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li <?php echo (Core::isActiveLink(array('tutorial.php'))) ? ' class="active"' : ''; ?>><a
                            href="tutorial.php?token=<?php echo $token; ?>"><span class="menu-text">Tutorials</span></a>
                </li>
            </ul>
        </li>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View Telecaller Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View BD Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View Campaign Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View City Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View Product Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Month Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Reason Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Bank Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Segment Report')) ||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Priority Report'))||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Support Agent Report'))||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Escalate To Two Report'))||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Escalate To Three Report'))||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Average Response Time Report'))||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Agent Performance'))||
        ($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Question Error Report'))
    ) {
        ?>
        <li <?php echo (Utility::ParentMenuActive('REPORT', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-bar-chart"></i>
                <span class="menu-text">Reports</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li <?php echo (Utility::ParentMenuActive('REPORT', $current_page)) ? ' class="active open"' : ''; ?>>
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <i class="icon-table"></i>
                        <span class="menu-text">Support</span>
                        <b class="arrow icon-angle-down"></b>
                    </a>
                    <ul class="submenu">
                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Month Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('month_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="month_report.php?token=<?php echo $token; ?>">Month Report</a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Reason Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('reason_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="reason_report.php?token=<?php echo $token; ?>">Reason Report</a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Bank Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('bank_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="bank_report.php?token=<?php echo $token; ?>">Bank Report</a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Segment Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('segment_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="segment_report.php?token=<?php echo $token; ?>">Segment Report</a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Priority Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('priority_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="priority_report.php?token=<?php echo $token; ?>">Priority Report</a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Support Agent Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('support_agent_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="support_agent_report.php?token=<?php echo $token; ?>">Agent Report</a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Escalate To Two Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('escalate_to_2_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="escalate_to_2_report.php?token=<?php echo $token; ?>">Escalate To Two Report</a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Escalate To Three Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('escalate_to_3_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="escalate_to_3_report.php?token=<?php echo $token; ?>">Escalate To Three Report</a></li>
                        <?php } ?>

                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Average Resolve Time'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('avg_resolve_time.php'))) ? ' class="active"' : ''; ?>><a
                                        href="avg_resolve_time.php?token=<?php echo $token; ?>">View Average Resolve Time</a></li>
                        <?php } ?>
                    </ul>
                </li>

                <li <?php echo (Utility::ParentMenuActive('REPORT', $current_page)) ? ' class="active open"' : ''; ?>>
                    <a class="dropdown-toggle" href="javascript:void(0);">
                        <i class="icon-table"></i>
                        <span class="menu-text">Call Audit</span>
                        <b class="arrow icon-angle-down"></b>
                    </a>
                    <ul class="submenu">
                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Agent Performance'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('agent_performance.php'))) ? ' class="active"' : ''; ?>><a
                                        href="agent_performance.php?token=<?php echo $token; ?>">Agent Performance</a></li>
                        <?php } ?>
                        <?php
                        if (($acl->IsAllowed($login_id, 'REPORT', 'Report', 'View Question Error Report'))) {
                            ?>
                            <li <?php echo (Core::isActiveLink(array('question_error_report.php'))) ? ' class="active"' : ''; ?>><a
                                        href="question_error_report.php?token=<?php echo $token; ?>">Question Error Report</a></li>
                        <?php } ?>
                    </ul>
                </li>

                <?php
                if (($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View Telecaller Report'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('agent_report.php', 'agent_report_an.php'))) ? ' class="active"' : ''; ?>>
                        <a href="agent_report.php?token=<?php echo $token; ?>">Agent Report</a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View BD Report'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('bd_report.php'))) ? ' class="active"' : ''; ?>><a
                                href="bd_report.php?token=<?php echo $token; ?>">BD Report</a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View Campaign Report'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('campaign_report.php'))) ? ' class="active"' : ''; ?>><a
                                href="campaign_report.php?token=<?php echo $token; ?>">Campaign Report</a></li>
                <?php } ?>


                <?php
                if (($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View Product Report'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('product_report.php'))) ? ' class="active"' : ''; ?>><a
                                href="product_report.php?token=<?php echo $token; ?>">Product Report</a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'REPORT', 'REPORT', 'View City Report'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('city_report.php'))) ? ' class="active"' : ''; ?>><a
                                href="city_report.php?token=<?php echo $token; ?>">City Report</a></li>
                <?php } ?>

            </ul>
        </li>
    <?php } ?>

    <?php
    if ($acl->IsAllowed($login_id, 'ACTIVITY LOG', 'Activity Log', 'View Activity Log')
    ) {
        ?>
        <li<?php echo (Utility::ParentMenuActive('ACTIVITY LOG', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-list-alt"></i>
                <span class="menu-text">Activity Log</span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <li <?php echo (Core::isActiveLink(array('activity_log.php'))) ? ' class="active"' : ''; ?>><a
                            href="activity_log.php?token=<?php echo $token; ?>"><span class="menu-text">View Activity Log</span></a>
                </li>
            </ul>
        </li>
    <?php } ?>

    <?php
    if (($acl->IsAllowed($login_id, 'USERS', 'User Permission', 'View User Permission')) ||
        ($acl->IsAllowed($login_id, 'USERS', 'Role Permission', 'View Role Permission')) ||
        ($acl->IsAllowed($login_id, 'USERS', 'Users', 'View Users'))
    ) {
        ?>
        <li <?php echo (Utility::ParentMenuActive('user', $current_page)) ? ' class="active open"' : ''; ?>>
            <a class="dropdown-toggle" href="javascript:void(0);">
                <i class="icon-user"></i>
                <span class="menu-text"><?php echo Utility::userTypeLabel("user", $ses->Get("user_type")); ?></span>
                <b class="arrow icon-angle-down"></b>
            </a>
            <ul class="submenu">
                <?php
                if (($acl->IsAllowed($login_id, 'USERS', 'Users', 'View Users'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('users.php', 'user_add.php', 'user_edit.php'))) ? ' class="active"' : ''; ?>>
                        <a href="users.php?token=<?php echo $token; ?>">Manage <?php echo Utility::userTypeLabel("user", $ses->Get("user_type")); ?></a>
                    </li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'USERS', 'Role Permission', 'View Role Permission'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('role_permission_master.php', 'role_edit_permission.php', 'role_add_permission.php'))) ? ' class="active"' : ''; ?>>
                        <a href="role_permission_master.php?token=<?php echo $token; ?>">Role Permission Master</a></li>
                <?php } ?>

                <?php
                if (($acl->IsAllowed($login_id, 'USERS', 'User Permission', 'View User Permission'))) {
                    ?>
                    <li <?php echo (Core::isActiveLink(array('user_permission_master.php', 'user_add_permission.php', 'user_edit_permission.php'))) ? ' class="active"' : ''; ?>>
                        <a href="user_permission_master.php?token=<?php echo $token; ?>">User Permission Master</a></li>
                <?php } ?>


            </ul>
        </li>
    <?php } ?>


</ul>
