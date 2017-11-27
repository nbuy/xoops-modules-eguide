<div class="event">
    <div class="clearfix">
        <h3 style="float:left;"><a href="event.php?eid=<{$event.eid}><{if $event.exid}>&amp;sub=<{$event.exid}><{/if}>"
                                   class="evhead">
                <{$event.date}> &nbsp; <{$event.title}></a></h3>
        <div class="evmark" style="float:right;margin-top:20px;"><{$event.fill_mark}></div>
    </div>
    <{if $event.catname}>
        <div style="float:right;">
            <a href="index.php?cat=<{$event.catid}>">
                <{if $event.catimg}>
                    <img src="<{$event.catimg}>" alt="<{$event.catname}>" class="catbox img-thumbnail" width="150">
                <{else}>
                    <span class="catbox"><{$event.catname}></span>
                <{/if}>
            </a>
        </div>
    <{/if}>
    <div class="evinfo"><{$smarty.const._MD_POSTERC}> <a
                href="<{$xoops_url}>/userinfo.php?uid=<{$event.uid}>"><{$event.uname}></a>&nbsp;&nbsp;<{$smarty.const._MD_POSTDATE}> <{$event.postdate}>
        (<{$event.hits}>)
    </div>
    <div class="evbody"><{$event.disp_summary}></div>
    <div class="evprop">
        <ul>
            <li><{$smarty.const._MD_STARTTIME}> <{$event.time}></li>
            <{if $event.persons}>
                <li><{$event.reserv_num}><{if $event.reservation}> (<{$event.reserv_reg}>)<{/if}></li>
            <{/if}>
            <{if $event.dispclose && $event.reservation}>
                <li><{$smarty.const._MD_CLOSEDATE}> <{$event.dispclose}></li>
            <{/if}>
            <{if $points_this}>
                <!-- points plugin -->
                <li><{$smarty.const._POINTS_NEED}> <{$points_this}></li>
            <{/if}>
        </ul>
    </div>
    <{if $event.disp_body}>
        <div class="evbody"><{$event.disp_body}></div>
    <{elseif $event.expire && $event.reservation}>
        <p><a href="event.php?eid=<{$event.eid}><{if $event.exid}>&amp;sub=<{$event.exid}><{/if}>#form"
              class="btn btn-primary" role="button"><{$smarty.const._MD_RESERV_FORM}></a></p>
    <{/if}>
    <{if $event.link}>
        <div class="evlink text-right"><{if $event.isadmin}>
                <{if $event.reservation || $event.reserved}>
                    <div class="btn-group">
                    <a href="receipt.php?eid=<{$event.eid}><{if $event.exid}>&amp;sub=<{$event.exid}><{/if}>"
                       class="btn btn-default btn-sm" role="button"><span
                                class="glyphicon glyphicon-th-list"></span> <{$smarty.const._MD_RESERV_ADMIN}></a>
                <{/if}>
                <a href="admin.php?eid=<{$event.eid}>" class="btn btn-default btn-sm" role="button"><span
                            class="glyphicon glyphicon-edit"></span> <{$smarty.const._EDIT}></a>
                <{if $event.extent}>
                    <a href="editdate.php?eid=<{$event.eid}>" class="btn btn-default btn-sm" role="button"><span
                                class="glyphicon glyphicon-calendar"></span> <{$smarty.const._MD_EDIT_EXTENT}></a>
                <{/if}>
                <a href="admin.php?op=delete&amp;eid=<{$event.eid}>" class="btn btn-default btn-sm" role="button"><span
                            class="glyphicon glyphicon-remove"></span> <{$smarty.const._DELETE}></a>
                </div>
            <{/if}>
            <div class="btn-group">
                <{if $caldate}>
                    <a href="<{$xoops_url}>/modules/piCal/index.php?caldate=<{$caldate}>" class="btn btn-default btn-sm"
                       role="button"><span class="glyphicon glyphicon-calendar"></span></a>
                <{/if}>
                <{if $event.detail}>
                    <a href="<{$event.detail}>" class="btn btn-default btn-sm"
                       role="button"><{$smarty.const._MD_READMORE}></a>
                <{else}>
                    <a href="event.php?op=print&amp;eid=<{$event.eid}><{if $event.exid}>&amp;sub=<{$event.exid}><{/if}>"
                       class="btn btn-default btn-sm" role="button"><span
                                class="glyphicon glyphicon-print"></span></a>
                <{/if}>
            </div>
        </div>
    <{/if}>
</div>
<hr>
