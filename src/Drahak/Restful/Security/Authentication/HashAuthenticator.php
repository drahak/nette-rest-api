<?php
namespace Drahak\Restful\Security\Authentication;

use Drahak\Restful\IAuthTokenCalculator;
use Drahak\Restful\IInput;
use Drahak\Restful\IRequestAuthenticator;
use Drahak\Restful\Security\AuthenticationException;
use Nette\Http\IRequest;
use Nette\Object;

/**
 * Verify request hashing data and comparing the results
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 */
class HashAuthenticator extends Object implements IRequestAuthenticator
{

	/** Auth token request header name */
	const AUTH_HEADER = 'X-HTTP-AUTH-TOKEN';

	/** @var array */
	private $privateKey;

	/** @var IRequest */
	protected $request;

	/** @var IAuthTokenCalculator */
	protected $calculator;

	public function __construct($privateKey, IRequest $request, IAuthTokenCalculator $calculator)
	{
		$this->privateKey = $privateKey;
		$this->request = $request;
		$this->calculator = $calculator;
	}

	/**
	 * @param IInput $input
	 * @return bool
	 * @throws AuthenticationException
	 */
	public function authenticate(IInput $input)
	{
		$requested = $this->getRequestedHash();
		$expected = $this->getExpectedHash($input);
		if (!$requested) {
			throw new AuthenticationException('Authentication header not found.');
		}

		if ($requested !== $expected) {
			throw new AuthenticationException('Authentication tokens do not match.');
		}
		return $requested;
	}


	/**
	 * Get request hash
	 * @return string
	 */
	protected function getRequestedHash()
	{
		return $this->request->getHeader(self::AUTH_HEADER);
	}

	/**
	 * Get expected hash
	 * @param IInput $input
	 * @return string
	 */
	protected function getExpectedHash(IInput $input)
	{
		return $this->calculator->calculate($input);
	}

}