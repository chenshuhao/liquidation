<?php
	namespace Liquidation\Alipay;

	class Alipay
	{
		public $sub_merchant_id;
		public $parent;

		static public $_self;

		static public function register($parent)
		{
			return function () use ($parent) {
				if (self::$_self) return self::$_self;

				self::$_self = new Alipay();
				self::$_self->sub_merchant_id = $parent->sub_merchant_id;
				self::$_self->parent = $parent;

				return self::$_self;
			};
		}

		public function tradePay($data)
		{
			$this->parent->setMethod('fshows.liquidation.submerchant.alipay.trade.pay');

			$data['sub_merchant'] = [
				'merchant_id' => $this->sub_merchant_id
			];

			return $this->parent->http($data);
		}

		public function tradePrecreate($data)
		{
			$this->parent->setMethod('fshows.liquidation.submerchant.alipay.trade.precreate');

			$data['sub_merchant'] = [
				'merchant_id' => $this->sub_merchant_id
			];

			return $this->parent->http($data);
		}
	}