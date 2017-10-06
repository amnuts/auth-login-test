<?php

namespace Components;

use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Parser;

class Token
{
    protected $jwt;
    protected $alg;

    private $algorithms = [
        'HS256' => 'Hmac\\Sha256',
        'HS384' => 'Hmac\\Sha384',
        'HS512' => 'Hmac\\Sha512',
        'RS256' => 'Rsa\\Sha256',
        'RS384' => 'Rsa\\Sha384',
        'RS512' => 'Rsa\\Sha512',
        'ES256' => 'Ecdsa\\Sha256',
        'ES384' => 'Ecdsa\\Sha384',
        'ES512' => 'Ecdsa\\Sha512'
    ];

    public function __construct($c, $jwtString, $alg = null)
    {
        try {
            $this->jwt = $jwtString;
            $this->alg = $alg;

            $this->alg = strtoupper((string)$alg);
            if (empty($this->alg) || !in_array($this->alg, $this->algorithms)) {
                $this->alg = 'HS512';
            }

        } catch (\Exception $e) {}
    }

    public function validate()
    {
        $decoded = null;
        try {
            $decoded = (new Parser())->parse($this->jwt);
        } catch (\Exception $e) {}
        $signer = "Lcobucci\\JWT\\Signer\\{$this->algorithms[$this->alg]}";
        return (!empty($decoded)
            && $decoded->verify(new $signer(), new Key(file_get_contents(__DIR__.'/../../../keys/hs512')))
        );
    }
}
