<!-- MODULE Block Last Visit -->
<div id="last-visits_block_right" class="block products_block">
    <h4 class="title_block">
        <a href="{$link->getPageLink('last-visits')|escape:'html'}" title="{l s='Recently Viewed Products' mod='blocklastvisit'}">
            {l s='Recently Viewed Products' mod='blocklastvisit'}
        </a>
    </h4>

    <div class="block_content">
        {if $last_visits && $last_visits|@count > 0}
            <ul class="product_images">
                {foreach from=$last_visits item=product name=myLoop}
                    <li class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} clearfix">
                        <a href="{$product.link|escape:'html'}" title="{$product.legend|escape:'html':'UTF-8'}" class="content_img clearfix">
                            <span class="number">{$smarty.foreach.myLoop.iteration}</span>
                            <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')|escape:'html'}"
                                 height="{$smallSize.height}" width="{$smallSize.width}"
                                 alt="{$product.legend|escape:'html':'UTF-8'}"/>

                        </a>
                        {if !$PS_CATALOG_MODE}
                        <p>
                            <a href="{$product.link|escape:'html'}" title="{$product.legend|escape:'html':'UTF-8'}">
                                {$product.name|strip_tags:'UTF-8'|escape:'html':'UTF-8'}<br/>
                                {if !$PS_CATALOG_MODE}
                                    <span class="price">{$product.price}</span>
                                    {hook h="displayProductPriceBlock" product=$product type="price"}
                                {/if}
                            </a>
                        </p>
                        {/if}
                    </li>
                {/foreach}
            </ul>

        {else}
            <p>{l s='There is no viewed products.' mod='blocklastvisit'}</p>
        {/if}
    </div>
</div>
<!-- /MODULE Block Last Visit -->
