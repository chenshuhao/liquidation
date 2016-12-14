<?php
	namespace Liquidation\Bill;

	class Bill
	{
		public $parent;

		static public $_self;

		static public function register($parent)
		{
			return function () use ($parent) {
				if(self::$_self) return self::$_self;

				self::$_self = new Bill();
				self::$_self->parent = $parent;

				return self::$_self;
			};
		}

		public function bill($bill_date, $pay_platform)
		{
			$this->parent->setMethod('fshows.liquidation.finance.downloadbill');

			$data['bill_date'] = $bill_date;
			$data['pay_platform'] = $pay_platform;

			return $this->parent->http($data);
		}
	}