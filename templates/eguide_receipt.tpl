<div class="evlist">
    <table width="100%">
        <tr>
            <td><a href="event.php?eid=<{$eid}><{if $exid}>&amp;sub=<{$exid}><{/if}>" class="evhead"><{$title}></a></td>
            <td class="text-right">
                <div class="btn-group">
                    <a href="admin.php?eid=<{$eid}>" class="btn btn-default" role="button"><{$smarty.const._EDIT}></a>
                    <{if $exid}><a href="editdate.php?eid=<{$eid}>" class="btn btn-default"
                                   role="button"><{$smarty.const._MD_EDIT_EXTENT}></a><{/if}>
                </div>
            </td>
        </tr>
    </table>
    <p>
    <{if $extents}>
    <table class="outer table table-bordered table-striped">
        <tr class="head">
            <th><{$smarty.const._MD_EXTENT_DATE}></th>
            <th><{$smarty.const._MD_ORDER_COUNT}></th>
            <th><{$smarty.const._MD_RESERV_PERSONS}></th>
            <th><{$smarty.const._MD_OPERATION}></th>
        </tr>
        <{foreach from=$extents item=ext}>
            <tr class="<{cycle values="even, odd"}>">
                <td><a href="receipt.php?eid=<{$eid}>&amp;sub=<{$ext.exid}>"><{$ext.date}></a></td>
                <td align="right"><{$ext.reserved}></td>
                <td align="right"><{$ext.persons}></td>
                <td align="center">
                    <a href="entry.php?eid=<{$eid}>&amp;sub=<{$ext.exid}>"><{$smarty.const._MD_RESERV_REGISTER}></a>
                </td>
            </tr>
        <{/foreach}>
    </table>
    <{else}>
    <h3 class="page-header"><{$smarty.const._MD_RESERV_LIST}></h3>

    <table width="100%" style="margin-bottom:5px;">
        <tr>
            <td><a href="entry.php?eid=<{$eid}><{if $exid}>&amp;sub=<{$exid}><{/if}>" class="btn btn-default btn-xs"
                   role="button"><{$smarty.const._MD_RESERV_REGISTER}></a>&nbsp;
                <{$smarty.const._MD_ORDER_COUNT}> <{$order_count}> (<{$reserv_num}>)
            </td>
            <td align="right"><{$smarty.const._MD_PRINT_DATE}> <{$print_date}></td>
        </tr>
    </table>
    <{if $list}>
        <form action="receipt.php" method="post">
            <table width="100%" cellspacing="1" cellpadding="4" class="outer table table-bordered table-striped">
                <tr class="head">
                    <th><{$smarty.const._MD_OPERATION}></th>
                    <th><{$smarty.const._MD_ORDER_DATE}></th>
                    <th><{$smarty.const._MD_STATUS}></th>
                    <{foreach from=$labels item=lab}>
                        <th><{$lab}></th>
                    <{/foreach}>
                </tr>
                <{foreach from=$list item=order}>
                <tr class="<{cycle values="even, odd"}>">
                    <td>
                        <{if $order.confirm}>
                            <input type="checkbox" name="act[]" value="<{$order.rvid}>" checked>
                        <{/if}>
                        <a href="receipt.php?rvid=<{$order.rvid}>" class="btn btn-info btn-xs"
                           role="button"><{$smarty.const._MD_DETAIL}></a>
                        <a href="receipt.php?op=edit&rvid=<{$order.rvid}>" class="btn btn-primary btn-xs"
                           role="button"><{$smarty.const._EDIT}></a>
                        <a href="reserv.php?op=cancel&rvid=<{$order.rvid}>" class="btn btn-danger btn-xs"
                           role="button"><{$smarty.const._DELETE}></a>
                    </td>
                    <td><{$order.date}></td>
                    <td><{$order.stat}></td>
                    <td>
                        <{if $order.uname}>
                            (
                            <a href="<{$xoops_url}>/userinfo.php?uid=<{$order.uid}>"><{$order.uname}></a>
                            )
                        <{/if}>
                        <{if $order.email}>
                            <a href="mailto:<{$order.email}>"><{$order.email}></a>
                        <{/if}>
                    </td>
                    <{foreach from=$order.add item=value}>
                        <td><{$value}></td>
                    <{/foreach}>
                    <{/foreach}>
                <tr>
            </table>
            <{if $confirm}>
                <input type='hidden' name='op' value='active'>
                <input type='hidden' name='eid' value='<{$eid}>'>
                <input type='hidden' name='sub' value='<{$exid}>'>
                <br>
                <div class="form-inline form-group">
                    <select name='yesno' class="form-control">
                        <{foreach from=$operations key=value item=label}>
                            <option value="<{$value}>"><{$label}></option>
                        <{/foreach}>
                    </select>
                    <input type="submit" value="<{$smarty.const._SUBMIT}>" class="btn btn-default">
                </div>
                <div class="form-group">
                    <label><{$smarty.const._MD_RESERV_MSG_H}></label>
                    <textarea name="msg" cols="60" rows="10" class="form-control"><{$reserv_msg}></textarea>
                </div>
            <{/if}>
        </form>
        <div class="text-right">
            <div class="btn-group">
                <a href="receipt.php?op=csv&amp;eid=<{$eid}><{if $exid}>&amp;sub=<{$exid}><{/if}>"
                   class="btn btn-default btn-sm" role="button"><{$smarty.const._MD_CSV_OUT}></a>
                <a href="export.php?eid=<{$eid}><{if $exid}>&amp;sub=<{$exid}><{/if}>" class="btn btn-default btn-sm"
                   role="button"><{$smarty.const._MD_EXPORT_OUT}></a>
                <a href="sendinfo.php?eid=<{$eid}><{if $exid}>&amp;sub=<{$exid}><{/if}>" class="btn btn-default btn-sm"
                   role="button"><{$smarty.const._MD_INFO_MAIL}></a>
            </div>
            <div class="btn-group">
                <a href="receipt.php?op=print&amp;eid=<{$eid}><{if $exid}>&amp;sub=<{$exid}><{/if}>"
                   class="btn btn-default btn-sm" role="button"><span class="glyphicon glyphicon-print"></span></a>
            </div>
        </div>
    <{else}>
        <div class="alert alert-warning text-center"><{$smarty.const._MD_NODATA}></div>
    <{/if}>
    <{if $citem}>
        <div class="evhead"><h3><{$smarty.const._MD_SUMMARY}></h3></div>
        <table class="evtbl table table-bordered table-striped">
            <tr class="head">
                <th><{$smarty.const._MD_SUM_ITEM}></th>
                <th><{$smarty.const._MD_SUM}></th>
            </tr>
            <{foreach from=$citem key=label item=count}>
                <tr class="<{cycle values="even, odd"}>">
                    <td><{$label}></td>
                    <td align='right'><{$count}></td>
                </tr>
            <{/foreach}>
        </table>
    <{/if}>
</div>
<{/if}>
