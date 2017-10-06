<?php

namespace Components;

use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Builder;

class Token
{
    protected $user;

    public function __construct($c, $user)
    {
        $this->user = $user;
    }

    public function generate()
    {
        return (new Builder())
            ->setAudience('http://localhost:8010')
            ->setIssuer('http://localhost:8020')
            ->setId(uniqid())
            ->setIssuedAt((new \DateTime('now'))->getTimestamp())
            ->setNotBefore((new \DateTime('now'))->getTimestamp())
            ->set('user', (object)[
                'id' => $this->user['id'],
                'email' => $this->user['email'],
                'firstname' => $this->user['firstname'],
                'surname' => $this->user['surname']
            ])
            ->sign(new Sha512(), new Key(file_get_contents(__DIR__.'/../../../keys/hs512')))
            ->getToken();
    }
}
