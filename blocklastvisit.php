<?php


if (!defined('_PS_VERSION_'))
	exit;

require 'library/BlockLastVisitCookie.php';

class BlockLastVisit extends Module
{
	protected static $cache_last_visits;
	protected $cookiem;
	protected $cookie_name;

	public function __construct()
	{
		$this->name 			= 'blocklastvisit';
		$this->tab 				= 'front_office_features';
		$this->version 			= '1.0.0';
		$this->author 		 	= 'PRESTATR';
		$this->need_instance	= 0;
		$this->bootstrap 		= true;
		$this->cookie_name 		= 'ps_cookie_products';

		parent::__construct();

		$this->displayName = $this->l('Last Visit Products Block');
		$this->description = $this->l('Adds a block displaying your store\'s last visiting products.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

		$this->cookiem = new BlockLastVisitCookie(new Cookie($this->cookie_name), (int)Configuration::get('PS_BLOCK_BESTSELLERS_TO_DISPLAY'));

	}

	/**
	 * install
	 * @return bool
	 */
	public function install()
	{
		$this->_clearCache('*');

		if (!parent::install()
			|| !$this->registerHook('header')
			|| !$this->registerHook('leftColumn')
			|| !$this->registerHook('addproduct')
			|| !$this->registerHook('updateproduct')
			|| !$this->registerHook('deleteproduct')
			|| !$this->registerHook('displayHomeTab')
			|| !$this->registerHook('displayHomeTabContent')
			|| !ProductSale::fillProductSales()
		)
			return false;

		Configuration::updateValue('PS_BLOCK_LAST_VISIT_TO_DISPLAY', 10);

		return true;
	}

	/**
	 * uninstall
	 * @return mixed
	 */
	public function uninstall()
	{
		$this->_clearCache('*');

		return parent::uninstall();
	}

	/**
	 * hookAddProduct
	 * @param $params
	 */
	public function hookAddProduct($params)
	{
		$this->_clearCache('*');
	}

	/**
	 * hookUpdateProduct
	 * @param $params
	 */
	public function hookUpdateProduct($params)
	{
		$this->_clearCache('*');
	}

	/**
	 * hookDeleteProduct
	 * @param $params
	 */
	public function hookDeleteProduct($params)
	{
		$this->_clearCache('*');
	}


	/**
	 * _clearCache
	 * @param $template
	 * @param null $cache_id
	 * @param null $compile_id
	 */
	public function _clearCache($template, $cache_id = null, $compile_id = null)
	{
		parent::_clearCache('blocklastvisit.tpl', 'blocklastvisit-col');
		parent::_clearCache('blocklastvisit-home.tpl', 'blocklastvisit-home');
		parent::_clearCache('tab.tpl', 'blocklastvisit-tab');
	}

	/**
	 * getContent
	 * @return string
	 */
	public function getContent()
	{
		$output = '';
		if (Tools::isSubmit('submitLastVisit'))
		{
			Configuration::updateValue('PS_BLOCK_LASTVISIT_DISPLAY', (int)Tools::getValue('PS_BLOCK_LASTVISIT_DISPLAY'));
			Configuration::updateValue('PS_BLOCK_LASTVISIT_TO_DISPLAY', (int)Tools::getValue('PS_BLOCK_LASTVISIT_TO_DISPLAY'));
			$this->_clearCache('*');
			$output .= $this->displayConfirmation($this->l('Settings updated'));
		}

		return $output.$this->renderForm();
	}

	/**
	 * renderForm
	 * @return mixed
	 */
	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Products to display'),
						'name' => 'PS_BLOCK_LASTVISIT_TO_DISPLAY',
						'desc' => $this->l('Number of displayed products.'),
						'class' => 'fixed-width-xs',
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Always display this block'),
						'name' => 'PS_BLOCK_LASTVISIT_DISPLAY',
						'desc' => $this->l('Show the block even if no last visit is available.'),
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					)
				),
				'submit' => array(
					'title' => $this->l('Save')
				)
			)
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitLastVisit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	/**
	 * getConfigFieldsValues
	 * @return array
	 */
	public function getConfigFieldsValues()
	{
		return array(
			'PS_BLOCK_LASTVISIT_TO_DISPLAY' => (int)Tools::getValue('PS_BLOCK_LASTVISIT_TO_DISPLAY', Configuration::get('PS_BLOCK_LASTVISIT_TO_DISPLAY')),
			'PS_BLOCK_LASTVISIT_DISPLAY' => (int)Tools::getValue('PS_BLOCK_LASTVISIT_DISPLAY', Configuration::get('PS_BLOCK_LASTVISIT_DISPLAY')),
		);
	}

	/**
	 * hookHeader
	 * @param $params
	 */
	public function hookHeader($params)
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return;

		if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'index') {
			$this->context->controller->addCSS(_THEME_CSS_DIR_ . 'product_list.css');
		}

		if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'product') {
			$this->cookiem->setData((int) Tools::getValue('id_product'));
		}

		$this->context->controller->addCSS($this->_path.'blocklastvisit.css', 'all');
	}

	/**
	 * hookDisplayHomeTab
	 * @param $params
	 * @return bool
	 */
	public function hookDisplayHomeTab($params)
	{
		if (!$this->isCached('tab.tpl', $this->getCacheId('blocklastvisit-tab')))
		{
			BlockLastVisit::$cache_last_visits = $this->getLastVisits($params);
			$this->smarty->assign('last_visit', BlockLastVisit::$cache_last_visits);
		}

		if (BlockLastVisit::$cache_last_visits === false)
			return false;

		return $this->display(__FILE__, 'tab.tpl', $this->getCacheId('blockalastvisit-tab'));
	}

	/**
	 * hookDisplayHome
	 * @param $params
	 * @return bool
	 */
	public function hookDisplayHome($params)
	{
		if (!$this->isCached('blocklastvisit-home.tpl', $this->getCacheId('blocklastvisit-home')))
		{
			$this->smarty->assign(array(
				'last_visits' => BlockLastVisit::$cache_last_visits,
				'homeSize' => Image::getSize(ImageType::getFormatedName('home'))
			));
		}

		if (BlockLastVisit::$cache_last_visits === false)
			return false;

		return $this->display(__FILE__, 'blocklastvisit-home.tpl', $this->getCacheId('blocklastvisit-home'));
	}

	/**
	 * hookDisplayHomeTabContent
	 * @param $params
	 * @return bool
	 */
	public function hookDisplayHomeTabContent($params)
	{
		return $this->hookDisplayHome($params);
	}

	/**
	 * hookRightColumn
	 * @param $params
	 * @return bool
	 */
	public function hookRightColumn($params)
	{
		if (!$this->isCached('blocklastvisit.tpl', $this->getCacheId('blocklastvisit-col')))
		{
			if (!isset(BlockLastVisit::$cache_last_visits))
				BlockLastVisit::$cache_last_visits = $this->getLastVisits($params);
			$this->smarty->assign(array(
				'last_visits' => BlockLastVisit::$cache_last_visits,
				'mediumSize' => Image::getSize(ImageType::getFormatedName('medium')),
				'smallSize' => Image::getSize(ImageType::getFormatedName('small'))
			));
		}

		if (BlockLastVisit::$cache_last_visits === false)
			return false;

		return $this->display(__FILE__, 'blocklastvisit.tpl', $this->getCacheId('blocklastvisit-col'));
	}

	/**
	 * hookLeftColumn
	 * @param $params
	 * @return bool
	 */
	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

	/**
	 * getLastVisitsProducts
	 * @param $id_lang
	 * @param array $product_by_ids
	 * @param int $limit
	 * @param Context|null $context
	 * @return bool
	 * @throws PrestaShopDatabaseException
	 */
	protected function getLastVisitsProducts($id_lang, array $product_by_ids, $limit = 10,  Context $context = null)
	{

		if (!$context) {
			$context = Context::getContext();
		}

		if(!count($product_by_ids))
		{
			return false;
		}

		$sql = '
		SELECT
			p.id_product, IFNULL(product_attribute_shop.id_product_attribute,0) id_product_attribute, pl.`link_rewrite`, pl.`name`, pl.`description_short`, product_shop.`id_category_default`,
			image_shop.`id_image` id_image, il.`legend`,
			ps.`quantity` AS sales, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category, p.show_price, p.available_for_order, IFNULL(stock.quantity, 0) as quantity, p.customizable,
			IFNULL(pa.minimal_quantity, p.minimal_quantity) as minimal_quantity, stock.out_of_stock,
			product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'" as new,
			product_shop.`on_sale`, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity
		FROM `'._DB_PREFIX_.'product_sale` ps
		LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
		'.Shop::addSqlAssociation('product', 'p').'
		LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
			ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id.')
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (product_attribute_shop.id_product_attribute=pa.id_product_attribute)
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
			ON p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
		LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
			ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
			ON cl.`id_category` = product_shop.`id_category_default`
			AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').Product::sqlStock('p', 0);

		$sql .= '
		WHERE product_shop.`active` = 1 and p.`id_product` IN ('.implode(',', $product_by_ids).')
		AND p.`visibility` != \'none\'';

		if (Group::isFeatureActive()) {
			$groups = FrontController::getCurrentCustomerGroups();
			$sql .= ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
				JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').')
				WHERE cp.`id_product` = p.`id_product`)';
		}

		$sql .= '
		ORDER BY FIELD(p.id_product, '.implode(',', $product_by_ids).')
		LIMIT '.(int)$limit;

		if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
			return false;
		}

		return Product::getProductsProperties($id_lang, $result);

	}

	/**
	 * getLastVisits
	 * @param $params
	 * @return bool
	 */
	protected function getLastVisits($params)
	{

		if (Configuration::get('PS_CATALOG_MODE')) {
			return false;
		}

		$products_ids = $this->cookiem->readData($this->cookie_name);

		if (!($results = $this->getLastVisitsProducts((int)$params['cookie']->id_lang, $products_ids, (int)Configuration::get('PS_BLOCK_BESTSELLERS_TO_DISPLAY')))){
			return (Configuration::get('PS_BLOCK_LASTVISIT_DISPLAY') ? array() : false);
		}

		$currency = new Currency($params['cookie']->id_currency);
		$usetax   = (Product::getTaxCalculationMethod((int)$this->context->customer->id) != PS_TAX_EXC);

		foreach ($results as $row) {
			$row['price'] = Tools::displayPrice(Product::getPriceStatic((int)$row['id_product'], $usetax), $currency);
		}

		return $results;
	}
}
