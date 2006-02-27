<?php
// $Id: modinfo.php,v 1.6 2006/02/27 17:32:43 nobu Exp $
// Module Info

// The name of this module
define("_MI_EGUIDE_NAME","Event Guide");

// A brief description of this module
define("_MI_EGUIDE_DESC","Event Detail display and Reservation system");

// Names of blocks for this module (Not all module has blocks)
define("_MI_EGUIDE_MYLIST","Reserved Events");
define("_MI_EGUIDE_SUBMIT","Register New Event");
define("_MI_EGUIDE_REG","Notify me of new events");
define("_MI_EGUIDE_HEADLINE","Event Guide");
define("_MI_EGUIDE_HEADLINE_DESC","Upcomming Recent Event List");
define("_MI_EGUIDE_HEADLINE2","New Events");
define("_MI_EGUIDE_HEADLINE2_DESC","Newer Posted Event List");

define("_MI_EGUIDE_CONFIG","Module Configuration");
define("_MI_EGUIDE_EVENTS","Event article Operation");
define("_MI_EGUIDE_NOTIFIES","Notify to New registers");
define("_MI_EGUIDE_CATEGORY","Event Categories");

// Configuration variable for this module
define("_MI_EGUIDE_POSTGROUP","Group of Event Post");
define("_MI_EGUIDE_POSTGROUP_DESC","Set a group the owner permited to administration for own evnet.");
define("_MI_EGUIDE_NOTIFYADMIN","Notify mail to Admin");
define("_MI_EGUIDE_NOTIFYADMIN_DESC","Notification mail to admin when New Event registerd");
define("_MI_EGUIDE_NOTIFYGROUP","Admin Group for Notification");
define("_MI_EGUIDE_NOTIFYGROUP_DESC","The group is received admin notification mail");
define("_MI_EGUIDE_NEEDPOSTAUTH","Do you need to approve New Event");
define("_MI_EGUIDE_NEEDPOSTAUTH_DESC","Need to approve New Event by site administrator");
define("_MI_EGUIDE_MAX_LISTITEM","Display additional items in list");
define("_MI_EGUIDE_MAX_LISTITEM_DESC","Display items entry order additional form");
define("_MI_EGUIDE_MAX_LISTLINES","Display list items in a page");
define("_MI_EGUIDE_MAX_LISTLINES_DESC","How many item lines in a page");
define("_MI_EGUIDE_MAX_EVENT","Display events in top page");
define("_MI_EGUIDE_MAX_EVENT_DESC","Number of listed events in top page");
define("_MI_EGUIDE_SHOW_EXTENTS","Show Multiple Entry");
define("_MI_EGUIDE_SHOW_EXTENTS_DESC","When event has multiple entry, show each entris. YES - display each entries. NO - Only show recenet entry.");
define("_MI_EGUIDE_USER_NOTIFY","User requested notification of new event");
define("_MI_EGUIDE_USER_NOTIFY_DESC","YES - Enable notification mail, NO - disable.");
define("_MI_EGUIDE_MEMBER","Event entry need to LOGIN");
define("_MI_EGUIDE_MEMBER_DESC","Only login user can be entry event. (No entry email address)");
define("_MI_EGUIDE_CLOSEBEFORE","Close Time Before (min)");
define("_MI_EGUIDE_CLOSEBEFORE_DESC","Event entry close time before setting minits.");
define("_MI_EGUIDE_PLUGINS","Use Other moudle plugins");
define("_MI_EGUIDE_PLUGINS_DESC","Internal Control accept entry form other modules");
define("_MI_EGUIDE_COMMENT","Allow Comments");
define("_MI_EGUIDE_COMMENT_DESC","Allow commnets to event");
define("_MI_EGUIDE_MARKER","Current entry level mark");
define("_MI_EGUIDE_MARKER_DESC","The mark mean of how many entry in current. Show mark correspond parcentage. (xx,yy mean less than xx% showup yy. And '0,yy' mean out of date mark)");
define("_MI_EGUIDE_MARKER_DEF","0,[Close]\n50,[Empty]\n100,[Many]\n101,[Full]\n");
// Templates
define("_MI_EGUIDE_INDEX_TPL", "Event Guide Top page list");
define("_MI_EGUIDE_EVENT_TPL", "Detail of Event");
define("_MI_EGUIDE_EVENT_PRINT_TPL", "Detail of Event for Print");
define("_MI_EGUIDE_RECEIPT_TPL", "Reservations List");
define("_MI_EGUIDE_ADMIN_TPL", "Event Entry Form");
define("_MI_EGUIDE_RECEIPT_PRINT_TPL", "Reservations List for Print");
define("_MI_EGUIDE_EVENT_ITEM_TPL", "Item of Event Showup");
define("_MI_EGUIDE_EVENT_CONF_TPL", "Event Confirmation Form");
define("_MI_EGUIDE_EVENT_LIST_TPL", "Reserved Event List");
?>