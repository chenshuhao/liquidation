<?php

    namespace Liquidation\Callback;

    class Callback
    {
        public $parent;

        static public $_self;

        static public function register($parent)
        {
            return function () use ($parent) {
                if (self::$_self) return self::$_self;

                self::$_self = new Callback();
                self::$_self->parent = $parent;

                return self::$_self;
            };
        }

        public function callback($callback)
        {
            if($this->verifySign()){
//                return $callback($this->param());
            }else{
//                throw new \Exception('签名错误');
            }
	        return $callback($this->param());

        }

        public function verifySign(){
            $data = $this->param();
            $sign = $data['sign'];
            unset($data['sign']);
            return $this->parent->verify($data,$sign);
        }

        public function param()
        {
            $data = file_get_contents('php://input');
            if ($data) {
                return json_decode($data, 1);
            } else {
                return $_POST;
            }
        }

    }