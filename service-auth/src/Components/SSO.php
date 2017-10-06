<?php

namespace Components;

class SSO
{
    protected $meta;

    public function __construct($c, $idpCode)
    {
        $idp = $c->db->querySingle("select * from saml_entities where code = '{$idpCode}'", true);
        $this->meta = $c->get('settings')['sso'];
        $this->meta['idp'] = [
            'entityId' => $idp['entity_id'],
            'singleSignOnService' => ['url' => $idp['sso']],
            'singleLogoutService' => ['url' => $idp['slo']],
            'x509cert' => $idp['cert'],
        ];
        array_walk_recursive($this->meta['sp'], function(&$v) use ($idpCode) {
            $v = sprintf($v, $idpCode);
        });
    }

    public function getSettings()
    {
        return $this->meta;
    }
}
