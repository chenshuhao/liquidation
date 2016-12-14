<?php
	namespace Liquidation\Submerchant;

	class Submerchant
	{

		public $app_id;
		public $sub_merchant_id;

		public $parent;

		static public $_self;

		static public function register($parent)
		{
			return function () use ($parent) {

				if(self::$_self) return self::$_self;

				self::$_self = new submerchant();
				self::$_self->app_id = $parent->app_id;
				self::$_self->sub_merchant_id = $parent->sub_merchant_id;
				self::$_self->parent = $parent;

				return self::$_self;
			};
		}

		public function create($data)
		{
			$this->parent->setMethod('fshows.liquidation.submerchant.create');

//			$data= [
//				/*必填*/
//				'external_id'=>'',
//				'name'=>'',
//				'alias_name'=>'',
//				'service_phone'=>'',
//				'category_id'=>'',
//				/*非必填*/
//				'contact_name'=>'',
//				'contact_phone'=>'',
//				'contact_mobile'=>'',
//				'contact_email'=>'',
//				'memo'=>'',
//			];

			$response = $this->parent->http($data);
			if ($response['success'] == 'true') {
				$this->sub_merchant_id = $response['return_value']['sub_merchant_id'];
			}

			return $response;
		}

		public function query($external_id)
		{
			$this->parent->setMethod('fshows.liquidation.submerchant.query');

			$data = [
				'sub_merchant_id' => $this->sub_merchant_id,
				'external_id'     => $external_id
			];

			return $this->parent->http($data);

		}

		public function bankBind($bank_card_no, $card_holder)
		{
			$this->parent->setMethod('fshows.liquidation.submerchant.bank.bind');

			$data['sub_merchant_id'] = $this->sub_merchant_id;
			$data['bank_card_no'] = $bank_card_no;
			$data['card_holder'] = $card_holder;

			return $this->parent->http($data);
		}

//		public function downloadbill(){
//			$this->parent->setMethod('fshows.liquidation.finance.downloadbill');
//			$data[] = ;
//		}

	}