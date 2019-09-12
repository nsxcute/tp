<?php
namespace app\index\controller;
use think\Controller;
class Encapsulation extends Controller
{
	private $headers;

	private $claims;

	private $signature;

	private $encoder;

	private $claimFactory;

	public function __construct(Encoder $encoder = null, ClaimFactory $claimFactory = null){
		$this->encoder = $encoder ?: new ClaimFactory();
		
	}
	public function test()
	{
		
	}
}