<?php

namespace Components;

use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Builder;

class Token
{
    protected $user;

    /**
     * Token constructor.
     *
     * @param mixed $c The Slim contains (Pimple)
     * @param string $user The unique id of the user (in this case, email address)
     */
    public function __construct($c, $user)
    {
        $this->user = $user;
    }

    /**
     * Generate a new JWT token.
     *
     * Different claims could be put in here, but this is a viable minimum example.
     *
     * Key file is set to be HMAC512, though it could easily be change for RSA, or
     * different strengths (though not sure why it would want to be less strength).
     *
     * @return \Lcobucci\JWT\Token
     */
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
