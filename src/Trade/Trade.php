<?php
	namespace Liquidation\Trade;

	class Trade
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

		public function query($out_trade_no, $trade_no = false)
		{
			$this->parent->setMethod('fshows.liquidation.alipay.trade.query');

			if ($trade_no) {
				$data['trade_no'] = $out_trade_no;
			} else {
				$data['out_trade_no'] = $out_trade_no;
			}

			return $this->parent->http($data);
		}
	}