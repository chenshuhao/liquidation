<?php
	namespace Liquidation\Rate;

	class Rate
	{
		public $parent;
		public $sub_merchant_id;

		static public $_self;

		static public function register($parent)
		{
			return function () use ($parent) {
				if(self::$_self) return self::$_self;

				self::$_self = new Rate();
				self::$_self->sub_merchant_id = $parent->sub_merchant_id;
				self::$_self->parent = $parent;

				return self::$_self;
			};
		}

		public function set($sub_merchant_id, $merchant_rate, $external_id = NULL)
		{
			$this->parent->setMethod('fshows.liquidation.submerchant.rate.set');

			$data['merchant_rate'] = $merchant_rate;

			if ($external_id !== NULL) {
				$data['external_id'] = $sub_merchant_id;
			} else {
				$data['sub_merchant_id'] = $sub_merchant_id;
			}

			return $this->parent->http($data);
		}

		public function query($sub_merchant_id, $external_id = NULL)
		{
			$this->parent->setMethod('fshows.liquidation.submerchant.rate.query');

			if ($external_id !== NULL) {
				$data['external_id'] = $sub_merchant_id;
			} else {
				$data['sub_merchant_id'] = $sub_merchant_id;
			}

			return $this->parent->http($data);
		}
	}