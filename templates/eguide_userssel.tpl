<{include file="db:eguide_item.tpl"}>
<{if $errors}>
    <{foreach from=$errors item=msg}>
        <div class="error"><{$msg}></div>
    <{/foreach}>
<{/if}>
<{if $message}>
<div class="entform">
    <div class="evnote"><{$message}></div>
</div>
<{else}>
<div class="entlist">
    <div id="users_search">
        <form action="entry.php" class="form-inline">
            <{securityToken}><{*//mb*}>
            <input type="hidden" name="eid" value="<{$form.eid}>">
            <{if $event.exid}>
                <input type="hidden" name="sub" value="<{$event.exid}>">
            <{/if}>
            <input type="text" name="search" value="<{$smarty.get.search|escape}>" class="form-control"> <input
                    type="submit" value="<{$smarty.const._SEARCH}>" class="btn btn-default">
        </form>
    </div>
    <table width="100%">
        <tr>
            <td width="20%"><{$smarty.const._SEARCH}> <{$users_total}></td>
            <td align="center"><{$navigation}></td>
            <td width="20%"></td>
        </tr>
    </table>
    <table class="outer table table-bordered table-striped" align="center" cellspacing="1" border="0">
        <tr class="head">
            <th></th>
            <{foreach item=col from=$columns}>
                <th><{$lang_users[$col]|escape}></th>
            <{/foreach}>
        </tr>
        <{foreach item=user from=$users}>
            <tr class="<{cycle values="even,odd"}>">
                <td>
                    <a href="entry.php?eid=<{$form.eid}><{if $event.exid}>&amp;exid=<{$event.exid}><{/if}>&amp;uid=<{$user.uid}>"><{$smarty.const._MD_RESERV_REGISTER}></a>
                    <{if $xoops_isadmin && $user.uid>0}>|
                        <a href="mylist.php?eid=<{$form.eid}><{if $event.exid}>&amp;exid=<{$event.exid}><{/if}>&amp;uid=<{$user.uid}>"><{$smarty.const._MD_DETAIL}></a>
                    <{/if}>
                </td>
                <{foreach item=col from=$columns}>
                    <td><{$user[$col]|escape}></td>
                <{/foreach}>
            </tr>
        <{/foreach}>
    </table>
    <{/if}>
