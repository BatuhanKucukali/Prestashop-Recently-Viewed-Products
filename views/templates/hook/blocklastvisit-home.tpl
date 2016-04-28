{if isset($last_visits) && $last_visits}
	{include file="$tpl_dir./product-list.tpl" products=$last_visits class='blocklastvisit tab-pane' id='blocklastvisit'}
{else}
<ul id="blocklastvisit" class="blocklastvisit tab-pane">
	<li class="alert alert-info">{l s='There is no viewed products.' mod='blocklastvisit'}</li>
</ul>
{/if}
