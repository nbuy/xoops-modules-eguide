<{include file="db:eguide_item.tpl"}>
<{if $errors}>
    <{foreach from=$errors item=msg}>
        <div class="error alert alert-danger text-center"><{$msg}></div>
    <{/foreach}>
<{/if}>
<{if $message}>
    <div class="entform">
        <div class="evnote alert alert-danger text-center"><{$message}></div>
    </div>
<{elseif $form}>
    <a name="form"></a>
    <div class="well">
        <div class="entform">
            <div class="evtitle"><h4><{$smarty.const._MD_RESERV_REGISTER}></h4></div>
            <form action="entry.php" method="post" class="form-inline">
                <{securityToken}><{*//mb*}>
                <table class="outer table table-bordered table-striped" align="center" cellspacing="1" border="0">
                    <tr>
                        <th colspan="2"><{$event.date}> &nbsp; <{$event.title}></th>
                    </tr>
                    <tr class="<{cycle values="even,odd"}>">
                        <td class="head"><{$smarty.const._MD_UNAME}></td>
                        <td><{$form.uname}></td>
                    </tr>
                    <{if !$form.member_only}>
                        <tr class="<{cycle values="even,odd"}>">
                            <td class="head"><{$smarty.const._MD_EMAIL}></td>
                            <td><input type="text" size="40" name="email" value="<{$form.email}>" class="form-control">
                            </td>
                        </tr>
                    <{/if}>
                    <{foreach from=$form.items item=fm}>
                        <tr class="<{$fm.attr}> <{cycle values="even,odd"}>">
                            <td class="head"><{$fm.label}></td>
                            <td><{$fm.value}><{$fm.comment}></td>
                        </tr>
                    <{/foreach}>
                </table>
                <{if $form.uid>0}>
                    <input type="hidden" name="uid" id="reserv_uid" value="<{$form.uid}>">
                <{/if}>
                <input type="hidden" name="eid" value="<{$form.eid}>">
                <{if $event.exid}>
                    <input type="hidden" name="sub" value="<{$event.exid}>">
                <{/if}>
                <p style="text-align: center;"><input type="submit" value="<{$smarty.const._MD_ORDER_SEND}>"
                                                      class="btn btn-primary"></p>
            </form>
        </div>
        <{if $form.note}>
            <div align='right'><span class="label label-danger"><{$form.note}></span></div>
        <{/if}>
    </div>
<{/if}>
