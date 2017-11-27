<{if $errors}>
    <{foreach from=$errors item=msg}>
        <div class="error"><{$msg}></div>
    <{/foreach}>
    <p>
<{/if}>
<table width="100%">
    <tr>
        <td>
            <a href="event.php?eid=<{$event.eid}>" class="evhead"><{$event.title}></a>
            (<{$smarty.const._MD_POSTDATE}> <{$event.postdate}>)
        </td>
        <td class="text-right">
            <div class="btn-group">
                <a href="admin.php?eid=<{$event.eid}>" role="button" class="btn btn-default"><{$smarty.const._EDIT}></a>
                <a href="export.php?eid=<{$event.eid}>" role="button"
                   class="btn btn-default"><{$smarty.const._MD_EXPORT_OUT}></a>
                <a href="collect.php?eid=<{$event.eid}>" role="button"
                   class="btn btn-default"><{$smarty.const._MD_SUMMARY}></a>
            </div>
        </td>
    </tr>
</table>
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
            href="<{$xoops_url}>/userinfo.php?uid=<{$event.uid}>"><{$event.uname}></a></div>
<p class="evbody"><{$event.disp_summary}></p>
<form method="post" class="form-inline">
    <div><{$smarty.const._MD_RESERV_PERSONS}>: <{$event.persons}><{$smarty.const._MD_RESERV_UNIT}></div>
    <table cellspacing="1" cellpading="2" class="outer table table-bordered table-striped">
        <tr>
            <th align="center"><{$labels[0]}></th>
            <th><{$labels[1]}></th>
            <th colspan="2"><{$labels[2]}></th>
            <th><{$labels[3]}></th>
        </tr>
        <{foreach from=$extents item=ext}>
            <tr class="<{cycle values="even, odd"}>">
                <td align="center"><{if $ext.reserved}>-<{else}><input type="checkbox" name="dels[<{$ext.exid}>]"
                                                                       value="<{$ext.exid}>"><{/if}></td>
                <td><a href="event.php?eid=<{$event.eid}>&amp;sub=<{$ext.exid}>"><{$ext.date}></a></td>
                <td align="right"><{$ext.reserved}></td>
                <td>(<{if $ext.expersons}><{$ext.expersons}><{else}>-<{/if}>)</td>
                <td align="center">
                    <{if $ext.edit}>
                        <input type="text" name="mods[<{$ext.exid}>]" value="<{$ext.edit}>" size="18"
                               class="form-control">
                        &nbsp;
                        <input type="text" name="exps[<{$ext.exid}>]" size="2" value="<{$ext.expersons}>"
                               class="form-control">
                    <{/if}>
                </td>
            </tr>
        <{/foreach}>
        </tr>
    </table>
    <h4><{$smarty.const._MD_ADD_EXTENT}></h4>
    <div><textarea name="adds" class="form-control"><{$adds}></textarea></div>
    <div class="evinfo"><{$smarty.const._MD_ADD_EXTENT_DESC}></div>
    <p><input type="submit" value="<{$smarty.const._MD_UPDATE}>" class="btn btn-primary"></p>
</form>
