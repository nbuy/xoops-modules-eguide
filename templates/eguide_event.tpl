<{include file="db:eguide_item.tpl"}>
<{if $event.extents}>
    <div class="evform form-inline">
        <form action="event.php">
            <{securityToken}><{*//mb*}>
            <b><{$smarty.const._MD_EXTENT_DATE}></b>
            <input type="hidden" name="eid" value="<{$event.eid}>">
            <select name="sub" class="form-control">
                <{foreach from=$event.extents item=ext}>
                    <option value="<{$ext.exid}>"><{$ext.date}></option>
                <{/foreach}>
            </select>
            <input type="submit" value="<{$smarty.const._SUBMIT}>" class="btn btn-default">
        </form>
    </div>
<{elseif $message}>
    <div class="evform">
        <div class="evnote alert alert-danger text-center"><{$message}></div>
    </div>
<{elseif $form}>
    <a name="form"></a>
    <div class="evform">
        <div class="evtitle"><h4><{$smarty.const._MD_RESERVATION}></h4></div>
        <form action="reserv.php?op=<{$form.op}>#form" name="order" method="post"
              onsubmit="return xoopsFormValidate_order();" class="form-inline">
            <table class="outer table table-bordered table-striped" align="center" cellspacing="1" border="0">
                <tr>
                    <th colspan="2"><{$event.date}> &nbsp; <{$event.title}></th>
                </tr>
                <{if !$form.member_only}>
                    <tr class="evms <{cycle values="even,odd"}>">
                        <td class="head"><{$smarty.const._MD_EMAIL}><{$smarty.const._MD_REQUIRE_MARK}></td>
                        <td><input type='text' size="40" name="email" id="email" value="<{$form.email}>"
                                   class="form-control">
                            <{if $form.user_notify}>
                                <br>
                                <label class="checkbox-inline"><input type="checkbox"
                                                                      <{if $smarty.post.notify}>checked="checked"
                                                                      <{/if}>name="notify"><{$smarty.const._MD_NOTIFY_REQUEST}>
                                </label>
                            <{/if}>
                        </td>
                    </tr>
                    <{if $form.options.email_repeat_check}>
                        <tr class="evms <{cycle values="even,odd"}>">
                            <td class="head"><{$smarty.const._MD_EMAIL_CONF}><{$smarty.const._MD_REQUIRE_MARK}></td>
                            <td><input type="text" size="40" name="email_conf" id="email_conf"
                                       value="<{$form.email_conf}>" class="form-control">
                                <div class="note"><{$smarty.const._MD_EMAIL_CONF_DESC}></div>
                            </td>
                        </tr>
                    <{/if}>
                <{/if}>
                <{foreach from=$form.items item=fm name=item}>
                    <{if preg_match("/^-/", $form.items[$smarty.foreach.item.iteration].label)}>
                        <{if empty($form_continue)}>
                            <tr class="<{$fm.attr}> <{cycle values="even,odd"}>">
                            <td class="head"><{$fm.label}></td>
                            <td>
                            <{assign var=form_continue value=1}>
                        <{/if}>
                        <{$fm.value}><{if $fm.comment}><span class="note"><{$fm.comment}></span><{/if}>
                    <{else}>
                        <{if !empty($form_continue)}>
                            <{$fm.value}><{if $fm.comment}><span class="note"><{$fm.comment}></span><{/if}>
                            </td></tr>
                            <{assign var=form_continue value=0}>
                        <{else}>
                            <tr class="<{$fm.attr}> <{cycle values="even,odd"}>">
                                <td class="head"><{$fm.label}></td>
                                <td><{$fm.value}><{if $fm.comment}><span class="note"><{$fm.comment}></span><{/if}></td>
                            </tr>
                        <{/if}>
                    <{/if}>
                <{/foreach}>
            </table>
            <input type="hidden" name="eid" value="<{$form.eid}>">
            <{if $event.exid}>
                <input type="hidden" name="sub" value="<{$event.exid}>">
            <{/if}>
            <p style="text-align: center;">

                <input type="submit"
                       value="<{if $form.op=="confirm"}><{$smarty.const._MD_ORDER_CONF}><{else}><{$smarty.const._MD_ORDER_SEND}><{/if}>" <{$form.submit_opts}>
                       class="btn btn-primary">

            </p>
        </form>
        <script type="text/javascript">
            <!--//
            function checkItem(obj) {
                if (typeof(obj.selectedIndex) == "number" && obj.value != "") return false;
                if (obj.value == "") return true;
                if (typeof(obj.length) == "number") {
                    for (i = 0; i < obj.length; i++) {
                        if (obj[i].checked) return false;
                    }
                    return true;
                }
                return false;
            }

            function xoopsFormValidate_order() {
                myform = window.document.order;
                obj = null;
                var msg = "";
                <{foreach from=$form.check key=name item=msg}>
                myobj = myform["<{$name}>"];
                if (checkItem(myobj)) {
                    msg = msg + "<{$msg}>\n";
                    if (obj == null) obj = myobj;
                }
                <{/foreach}>
                <{if $form.options.email_repeat_check}>
                myobj = myform["email_conf"];
                if (myform["email"].value != myobj.value) {
                    msg = msg + "<{$smarty.const._MD_MAIL_CONF_ERR}>\n";
                    if (obj == null) obj = myobj;
                }
                <{/if}>
                if (msg != "") {
                    window.alert(msg);
                    if (typeof(obj.length) != "number") obj.focus();
                    return false;
                }
                return true;
            }

            //--></script>
        <{if $form.lang_note || $form.note}>
            <p align="right"><span class="label label-danger"><{$form.lang_note}> <{$form.note}></span></p>
        <{/if}>
    </div>
<{/if}>

<{if $list}>
    <hr>
    <div class="evlist">
        <div class="evtitle"><h4><{$smarty.const._MD_RESERV_LIST}></h4></div>
        <table width="100%" cellspacing="1" cellpadding="4" class="outer table table-bordered table-striped">
            <tr class="head">
                <th></th><{foreach from=$labels item=lab}>
                <th><{$lab}></th><{/foreach}></tr>
            <{foreach from=$list item=order key=nc}>
            <tr class="<{cycle values="even,odd"}>">
                <td align="right"><{$nc}></td>
                <{foreach from=$order item=value}>
                    <td><{$value}></td><{/foreach}>
                <{/foreach}>
            <tr>
        </table>
    </div>
<{/if}>
<{if $commentsnav}>
    <!-- XOOPS common comment system -->
    <div style="text-align:center; margin-top:2em; margin-bottom:1em;">
        <{$commentsnav}>
        <{$lang_notice}>
    </div>
    <div class="evcomment">
        <!-- start comments loop -->
        <{if $comment_mode == "flat"}>
            <{include file="db:system_comments_flat.tpl"}>
        <{elseif $comment_mode == "thread"}>
            <{include file="db:system_comments_thread.tpl"}>
        <{elseif $comment_mode == "nest"}>
            <{include file="db:system_comments_nest.tpl"}>
        <{/if}>
        <!-- end comments loop -->
    </div>
<{/if}>
<{include file="db:system_notification_select.tpl"}>
