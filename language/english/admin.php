<?php
//%%%%%%	Admin Module Name  Articles 	%%%%%
define("_AM_DBUPDATED","Database Updated");
define("_AM_RVID","Reservation ID");
define("_AM_TITLE","Title");
define("_AM_POSTER","Poster");
define("_AM_DBDELETED","Event Deleted");
define("_AM_EDITARTICLE","Edit Event Article");
define("_AM_DATE_FMT","Y-m-d");
define("_AM_TIME_FMT","m-d H:i");
define("_AM_POST_FMT","Y-m-d H:i");
define("_AM_POSTED","Posted");
define("_AM_YES","Yes");
define("_AM_NO","No");
define("_AM_INTROTEXT","Introduction Text");
define("_AM_EXTEXT","Description");
define("_AM_OPERATION","Operation");
define("_AM_EDITUSER","More");
define("_AM_STATUS","Status");
define("_AM_EDIT","Status");
define("_AM_DISP_STATUS","Status");
define("_AM_ACTIVATE","Approved");
define("_AM_REFUSE","Refused");
define("_AM_ADD","Add");

define("_AM_APPROVE","Approve Display");
define("_AM_MONTHC","month %d");
define("_AM_DAYC","day %d");
define("_AM_YEARC","year %d");
define("_AM_TIMEC","Time");
define("_AM_PREVIEW","Preview");
define("_AM_SAVE","save");
define("_AM_SAVECHANGE","Save Changes");
define("_AM_CANCEL","Cancel");
define("_AM_NEWSUB","New Event");
define("_AM_NEW_NOTIFY","{SITENAME}
There is new event registered.\n
{TITLE}
  {EVENT_URL}\n
This event to be send before your registered address.
If you want no more notification, please remove register in follow URL:\n
  {CANCEL_URL}
");
define("_AM_NOTIFYSUBMIT","Notify mail to admin when New Event registerd");
define("_AM_NEEDPOSTAUTH","Need to approve New Event");
define("_AM_MAX_LISTITEM","Display additional item in list");
define("_AM_MAX_SHOW","Display events in top page");
define("_AM_USER_NOTIFY","Notfiy new event to requested user");
define("_AM_EMAIL","Email");

// for Event Guide
define("_AM_RESERVATION","Reservation");
define("_AM_RESERV_DESC","Doing reservation proceeding");
define("_AM_RESERV_STOP","Stop reservation when full of persons");
define("_AM_RESERV_AUTO","Accept Automatic (No need approve)");
define("_AM_RESERV_NOTIFY","Reservation notify by mail");
define("_AM_RESERV_NUM","Presons");
define("_AM_RESERV_UNIT","");
define("_AM_RESERV_ITEM","Additional Items");
define("_AM_RESERV_ITEM_DESC",'<a href="language/english/help.html#form" target="help">About Additional Items format</a>');
define("_AM_RESERV_DEFAULT_ITEM","Name*,size=40\nAddress\n");
define("_AM_RESERV_REC","Reservation Records");
define("_AM_RESERV_EDIT","Edit Reservation");
define("_AM_RESERV_DEL","Delete this");
define("_AM_RESERV_LIST","Return to list");

define("_AM_RESERV_MSG","{TITLE}\n{EVENT_URL}\n
for this event to order\n
    In {ORDER_MAIL}\n
reservation result are {RESULT}\n
Ordering Data:
------------
{INFO}------------
");
define("_AM_RESERV_MSG_H","Sending message in reservation");
define("_AM_RESERV_ACTIVE","ACCEPTED.");
define("_AM_RESERV_REFUSE","REFUSED.");

define("_AM_EVENT_DEL_DESC","Delete this event");
define("_AM_EVENT_DEL_ADMIN","Delete will be all of data include reservations.");
define("_AM_EVENT_DATE","Event Date Time");
define("_AM_EVENT_DAY","Event Date");
define("_AM_EVENT_EXPIRE","Finish Display");
define("_AM_EVENT_STYLE","Output Style");
define("_AM_POST_GROUP","Permit event register group");
define("_AM_RECEIPT","Reservations");
define("_AM_SUMMARY","Summary");
define("_AM_ORDER_COUNT","Count");
define("_AM_ORDER_DATE","Order Date");
define("_AM_PRINT_DATE","Print Date");
define("_AM_SUM_ITEM","Summary Item");
define("_AM_SUM","Sum");
define("_AM_CSV_OUT","CSV Output");
define("_AM_INFO_TITLE","Information Mail to Send");
define("_AM_INFO_MAIL","Sending Mail");
define("_AM_INFO_CONDITION","Send to");
define("_AM_INFO_REQUEST","Notify Registers");
define("_AM_INFO_COUNT","count %d");
define("_AM_INFO_SELF","send to self (%s)");
define("_AM_INFO_DEFAULT","-messages-\n\n\nReserved Event\n    {EVENT_URL}\n");
define("_AM_INFO_MAILOK","Mail sended");
define("_AM_INFO_MAILNG","Fail to send mail");

global $expire_set,$edit_style,$ev_stats;
$expire_set = array("+0"=>"Same Day", "+86400"=>"Next Day", "+172800"=>"2 days",
		    "+259200"=>"3 days","+604800"=>"1 week",
		    "+2592000"=>"1 month");
$edit_style=array(0=>"only XOOPS tags",
		  1=>"New line makes tag &lt;br&gt;",
		  2=>"disable HTML tags");
$ev_stats=array(0=>"display",
		1=>"waiting",
		4=>"deleted");
$rv_stats=array(0=>"waiting",
		1=>"reserved",
		2=>"refused");

?>