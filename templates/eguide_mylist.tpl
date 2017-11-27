<h2><{$smarty.const._MD_MYLIST}></h2>

<{if is_object($show_user)}>
    <{assign var="prog" value="receipt.php"}>
    <h3><{$smarty.const._MD_UNAME}>: <{$show_user->getVar('uname')}> (<{$show_user->getVar('name')}>)</h3>
<{else}>
    <{assign var="prog" value="event.php"}>
<{/if}>
<{if $reserved}>
    <table class="outer table table-bordered table-striped">
        <tr>
            <th><{$smarty.const._MD_ORDER_DATE}></th>
            <th><{$smarty.const._MD_TITLE}></th>
            <th></th>
        </tr>
        <{foreach from=$reserved item=r}>
            <tr class="<{cycle values="even, odd"}><{if $r.eid==$smarty.get.eid && (empty($r.exid) || $r.exid==$smarty.get.exid)}> select<{/if}>">
                <td><{$r.rdate_fmt}></td>
                <td><a href="<{$prog}>?eid=<{$r.eid}><{if $r.exid}>&amp;sub=<{$r.exid}><{/if}>"><{$r.edate_fmt}>
                        : <{$r.title}></a></td>
                <td><{if $r.cancel}><a href="reserv.php?op=cancel&amp;rvid=<{$r.rvid}>&amp;key=<{$r.confirm}>"
                                       class="btn btn-danger btn-xs"
                                       role="button"><{$smarty.const._MD_CANCEL}></a><{/if}></td>
            </tr>
        <{/foreach}>
    </table>
<{else}>
    <div class='evnote alert alert-warning text-center'><{$smarty.const._MD_NODATA}></div>
<{/if}>
