<?php
	/**
	 * Created by PhpStorm.
	 * User: china
	 * Date: 2016/12/14
	 * Time: 14:31
	 */

	namespace Liquidation\Jdpay;


	class Jdpay
	{
		public $sub_merchant_id;
		public $parent;

		static public $_self;

		static public function register($parent)
		{
			return function () use ($parent) {
				if(self::$_self) return self::$_self;

				self::$_self = new Jdpay();
				self::$_self->sub_merchant_id = $parent->sub_merchant_id;
				self::$_self->parent = $parent;

				return self::$_self;
			};
		}

		public function h5Pay($data)
		{
			$this->parent->setMethod('shows.liquidation.jdpay.h5pay');

			$data['sub_merchant_id'] = $this->sub_merchant_id;

			return $this->parent->http($data);
		}

		public function jumpUrl($data,$callback_url){

			return sprintf($this->parent->base_url.'/jdPayPage?prepay_id=%s&trade_no=%s&body=%s&callback_url=%s',
			               $data['prepay_id'],
			               $data['trade_no'],
			               $data['body'],
			               $callback_url
			);
		}

	}