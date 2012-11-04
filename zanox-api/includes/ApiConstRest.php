<?php

/**
 * Api Constants Enum Definitions for the REST interface
 *
 * Supported Version: PHP >= 5.1.0
 *
 * @author      Stefan Misch (stefan.misch@zanox.com)
 *
 * @see         http://wiki.zanox.com/en/Web_Services
 * @see         http://apps.zanox.com
 *
 * @package     ApiClient
 * @version     2011-03-01
 * @copyright   Copyright (c) 2007-2011 zanox.de AG
 */



/**
 * applicationTypeEnum
 */
		define('WIDGET', 'WIDGET');
		define('SAAS', 'SAAS');
		define('SOFTWARE', 'SOFTWARE');

/**
 * profileTypeEnum
 */
		define('PUBLISHER', 'PUBLISHER');
		define('ADVERTISER', 'ADVERTISER');

/**
 * programStatusEnum
 */
		define('ACTIVE', 'ACTIVE');
		define('INACTIVE', 'INACTIVE');

/**
 * programApplicationStatusEnum
 */
		define('OPEN', 'OPEN');
		define('CONFIRMED', 'CONFIRMED');
		define('REJECTED', 'REJECTED');
		define('DEFERRED', 'DEFERRED');
		define('WAITING', 'WAITING');
		define('BLOCKED', 'BLOCKED');
		define('TERMINATED', 'TERMINATED');
		define('CANCELED', 'CANCELED');
		define('CALLED', 'CALLED');
		define('DECLINED', 'DECLINED');
		define('DELETED', 'DELETED');

/**
 * admediaPurposeEnum
 */
		define('START_PAGE', 'START_PAGE');
		define('PRODUCT_DEEPLINK', 'PRODUCT_DEEPLINK');
		define('CATEGORY_DEEPLINK', 'CATEGORY_DEEPLINK');
		define('SEARCH_DEEPLINK', 'SEARCH_DEEPLINK');

/**
 * adspaceTypeEnum
 */
		define('WEBSITE', 'WEBSITE');
		define('EMAIL', 'EMAIL');
		define('SEARCH_ENGINE', 'SEARCH_ENGINE');

/**
 * adspaceScopeEnum
 */
		define('PRIVATE', 'PRIVATE');
		define('BUSINESS', 'BUSINESS');

/**
 * reviewStateEnum
 */
		define('CONFIRMED', 'CONFIRMED');
		define('OPEN', 'OPEN');
		define('REJECTED', 'REJECTED');
		define('APPROVED', 'APPROVED');

/**
 * admediaTypeEnum
 */
		define('HTML', 'HTML');
		define('SCRIPT', 'SCRIPT');
		define('LOOKAT_MEDIA', 'LOOKAT_MEDIA');
		define('IMAGE', 'IMAGE');
		define('IMAGE_TEXT', 'IMAGE_TEXT');
		define('TEXT', 'TEXT');

/**
 * searchTypeEnum
 */
		define('CONTEXTUAL', 'CONTEXTUAL');
		define('PHRASE', 'PHRASE');

/**
 * partnerShipEnum
 */
		define('DIRECT', 'DIRECT');
		define('INDIRECT', 'INDIRECT');

/**
 * dateTypeEnum
 */
		define('CLICK_DATE', 'CLICK_DATE');
		define('TRACKING_DATE', 'TRACKING_DATE');
		define('MODIFIED_DATE', 'MODIFIED_DATE');
		define('REVIEW_STATE_CHANGED_DATE', 'REVIEW_STATE_CHANGED_DATE');

/**
 * groupByEnum
 */
		define('CURRENCY', 'CURRENCY');
		define('ADMEDIUM', 'ADMEDIUM');
		define('PROGRAM', 'PROGRAM');
		define('ADSPACE', 'ADSPACE');
		define('LINK_FORMAT', 'LINK_FORMAT');
		define('REVIEW_STATE', 'REVIEW_STATE');
		define('TRACKING_CATEGORY', 'TRACKING_CATEGORY');
		define('MONTH', 'MONTH');
		define('DAY', 'DAY');
		define('YEAR', 'YEAR');
		define('DAY_OF_WEEK', 'DAY_OF_WEEK');
		define('APPLICATION', 'APPLICATION');
		define('MEDIA_SLOT', 'MEDIA_SLOT');

/**
 * incentiveTypeEnum
 */
		define('COUPONS', 'COUPONS');
		define('SAMPLES', 'SAMPLES');
		define('BARGAINS', 'BARGAINS');
		define('FREE_PRODUCTS', 'FREE_PRODUCTS');
		define('NO_SHIPPING_COSTS', 'NO_SHIPPING_COSTS');
		define('LOTTERIES', 'LOTTERIES');
		
		
/**
 * roleTypeEnum
 */

		define('DEVELOPER', 'DEVELOPER');
		define('CUSTOMER', 'CUSTOMER');
		define('TESTER', 'TESTER');

/**
 * settingTypeEnum
 */

		define('BOOLEAN', 'BOOLEAN');
		define('COLOR', 'COLOR');
		define('NUMBER', 'NUMBER');
		define('STRING', 'STRING');
		define('DATE', 'DATE');

/**
 * connectStatusTypeEnum
 */

		define('ACTIVE', 'ACTIVE');
		define('INACTIVE', 'INACTIVE');
		

/**
 * mediaSlotStatusEnum
 */
		define('ACTIVE', 'ACTIVE');
		define('DELETED', 'DELETED');

/**
 * transactionTypeEnum
 */
		define('LEADS', 'LEADS');
		define('SALES', 'SALES');




?>