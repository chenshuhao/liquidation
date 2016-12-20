<?php
	namespace Liquidation;

	class Initialization
	{
		static public $_self;
		public $app_id;
		public $sub_merchant_id;
		public $method;
		public $sign;
		public $version = '1.0';
		public $content;
		public $environment = 'DEV';

		public $private_key_file;
		public $private_key;

		public $public_key_file;
		public $public_key;

		public $liquidation_public_key_file;

		public $com = [
			'subMerchant' => Submerchant\Submerchant::class,
			'aliPay'      => Alipay\Alipay::class,
			'bill'        => Bill\Bill::class,
			'jdPay'       => Jdpay\Jdpay::class,
			'wxPay'       => Wxpay\Wxpay::class,
			'tate'        => Rate\Rate::class,
			'callback'    => Callback\Callback::class,
			'trade'       => Trade\Trade::class
		];

		//所有组件懒加载匿名函数
		public $app = [];
		public $base_url = "https://openapi-liquidation.51fubei.com";

		public $dev_url = 'https://openapi-liquidation-test.51fubei.com'; //测试地址

		public function __construct($app_id, $sub_merchant_id, $private_key_file, $liquidation_public_key_file, $run_model = "RUN")
		{
			$this->app_id = $app_id;
			$this->sub_merchant_id = $sub_merchant_id;
			$this->private_key_file = $private_key_file;
			$this->liquidation_public_key_file = $liquidation_public_key_file;
			$this->environment = $run_model;
			if ($this->environment != "RUN") {
				$this->base_url = $this->dev_url;
			}

			$this->register();
		}

		public function setSubMerchantId($sub_merchant_id)
		{
			$this->sub_merchant_id = $sub_merchant_id;
			$this->register();

			return $this;
		}

		static public function init($app_id, $sub_merchant_id, $private_key_file, $liquidation_public_key_file, $run_model = "RUN")
		{
			if (self::$_self == NULL) {
				self::$_self = new self($app_id, $sub_merchant_id, $private_key_file, $liquidation_public_key_file, $run_model);
			}

			return self::$_self;
		}

		public function setMethod($name)
		{
			$this->method = $name;
		}

		public function register()
		{
			foreach ($this->com as $k => $v) {
				$this->app[ $k ] = $v::register($this);
			}
		}

		public function sign($data)
		{
			$sign_arr = [];
			ksort($data);

			foreach ($data as $key => $value) {
				$sign_arr[] = "{$key}={$value}";
			}

			$sign_string = join('&', $sign_arr);

			$key_content = file_get_contents($this->private_key_file);

			if ($key_content) {
				$this->private_key = openssl_get_privatekey($key_content);
			} else {
				throw new \Exception('私钥文件无法读取');
			}

			if (openssl_sign($sign_string, $sign, $this->private_key)) {
				$this->sign = base64_encode($sign);
			} else {
				throw new \Exception('签名生产失败');
			}

		}

		public function getPostData($data)
		{

			$params = [
				'app_id'  => $this->app_id,
				'method'  => $this->method,
				'version' => $this->version,
				'content' => json_encode($data, 256)
			];

			$this->sign($params);
			$params['sign'] = $this->sign;

			return http_build_query($params);
		}

		public function http($data)
		{
			$postUrlParam = $this->getPostData($data);
            error($this->base_url . '/gateway' . '?' . $postUrlParam);
			$response = \Httpful\Request::post($this->base_url . '/gateway' . '?' . $postUrlParam)
				->send();

			return json_decode($response->raw_body, 1);
		}

		public function __get($name)
		{
			return $this->app[$name]();
		}

		public function verify($data, $sign)
		{
			ksort($data);
			$sign_arr = [];
			foreach ($data as $key => $value) {
				$sign_arr[] = "{$key}={$value}";
			}
			$sign_string = join('&', $sign_arr);

			$key_content = "-----BEGIN PUBLIC KEY-----\n" .
				wordwrap(str_replace(' ', '', $this->liquidation_public_key_file), 64, "\n", TRUE) .
				"\n-----END PUBLIC KEY-----";
			if ($key_content) {
				$this->public_key = openssl_get_publickey($key_content);
			} else {
				throw new \Exception('公钥文件无法读取');
			}

			return (bool)openssl_verify($sign_string, base64_decode($sign), $this->public_key);
		}


	}