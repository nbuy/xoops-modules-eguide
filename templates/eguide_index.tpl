<{include file='db:eguide_block_category.tpl' block=$categories}>
<{foreach from=$events item=event}>
    <{include file="db:eguide_item.tpl"}>
<{/foreach}>
<ul class="pager">
    <{if $page_prev}>
        <li class="previous"><a href="index.php<{$page_prev}>">&larr; <{$smarty.const._MD_SHOW_PREV}></a></li><{/if}>
    <{if $page_next}>
        <li class="next"><a href="index.php<{$page_next}>"><{$smarty.const._MD_SHOW_NEXT}> &rarr;</a></li><{/if}>
</ul>
<{include file='db:system_notification_select.tpl'}>
