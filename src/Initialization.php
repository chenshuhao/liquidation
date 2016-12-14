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
		public $environment = 'RUN';

		public $private_key_file;
		public $private_key;

		public $public_key_file;
		public $public_key;

		public $com = [
			'subMerchant' => Submerchant\Submerchant::class,
			'aliPay'      => Alipay\Alipay::class,
			'bill'        => Bill\Bill::class,
			'jdPay'       => Jdpay\Jdpay::class,
			'wxPay'       => Wxpay\Wxpay::class,
			'tate'        => Rate\Rate::class
		];

		//所有组件懒加载匿名函数
		public $app = [];

		public $dev = 'https://openapi-liquidation-test.51fubei.com/gateway'; //测试地址

		public $run = 'https://openapi-liquidation.51fubei.com/gateway';

		public function __construct($app_id, $sub_merchant_id, $private_key_file, $public_key_file)
		{
			$this->app_id = $app_id;
			$this->sub_merchant_id = $sub_merchant_id;
			$this->private_key_file = $private_key_file;
			$this->public_key_file = $public_key_file;

			$this->register();
		}

		static public function init($app_id, $sub_merchant_id, $private_key_file, $public_key_file)
		{
			if (self::$_self == NULL) {
				self::$_self = new self($app_id, $sub_merchant_id, $private_key_file, $public_key_file);
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
			var_dump($params);

			return http_build_query($params);
		}

		public function http($data)
		{
			$postUrlParam = $this->getPostData($data);
			$postUrl = $this->environment == 'DEV' ? $this->dev : $this->run;
			echo $postUrl . '?' . $postUrlParam;
			$response = \Httpful\Request::post($postUrl . '?' . $postUrlParam)
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

			$key_content = file_get_contents($this->public_key_file);

			if ($key_content) {
				$this->public_key = openssl_get_publickey($key_content);
			} else {
				throw new \Exception('公钥文件无法读取');
			}

			return (bool)openssl_verify($sign_string, base64_decode($sign), $this->public_key);
		}


	}