<?php
	/**
	 * Created by PhpStorm.
	 * User: china
	 * Date: 2016/12/14
	 * Time: 14:01
	 */

	namespace Liquidation\Wxpay;


	class Wxpay
	{
		public $sub_merchant_id;
		public $parent;

		public $prepay_id;

		static public $_self;

		static public function register($parent)
		{
			return function () use ($parent) {

				if (self::$_self) return self::$_self;

				self::$_self = new Wxpay();
				self::$_self->parent = $parent;
				self::$_self->sub_merchant_id = $parent->sub_merchant_id;

				return self::$_self;
			};
		}

		public function mpPay($data)
		{
			$this->parent->setMethod('fshows.liquidation.wxpay.mppay');
			/*
				body
				out_trade_no,
				total_fee,
				sub_openid,
				spbill_create_ip,
				notify_url,
				sub_appid
			*/
			$data['sub_merchant_id'] = $this->sub_merchant_id;
			var_dump($data);

			return $this->parent->http($data);
		}

		public function tradePay($data)
		{
			$this->parent->setMethod('fshows.liquidation.wx.trade.pay');

			/*
			body,out_trade_no,total_fee，spbill_create_ip，auth_code，sub_appid
			*/

			$data['store_id'] = $this->sub_merchant_id;
			$response = $this->parent->http($data);
			if ($response['success'] == TRUE) {
				$this->prepay_id = $response['return_value']['prepay_id'];
			}

			return $response;
		}

		public function tradePreCreate($data)
		{
			$this->parent->setMethod('fshows.liquidation.wx.trade.precreate');

			$data['store_id'] = $this->sub_merchant_id;

			return $this->parent->http($data);
		}

		public function appPay($data)
		{
			$this->parent->setMethod('fshows.liquidation.wxpay.apppay');

			$data['sub_merchant_id'] = $this->sub_merchant_id;

			return $this->parent->http($data);
		}

		public function h5Pay($prepay_id, $redirct_url)
		{
			return $this->parent->base_url . '/payPage/?prepay_id=' . ($prepay_id ?: $this->prepay_id) . '&callback_url=' . urlencode($redirct_url);
		}


	}