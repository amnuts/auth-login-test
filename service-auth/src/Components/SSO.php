<?php

namespace Components;

class SSO
{
    protected $meta;

    /**
     * SSO constructor.
     *
     * Given the user details, create an object that conforms to the required
     * configuration setup used by OneLogin's SAML library.  This means setting
     * up the idp and sp information, with the sp endpoints being dynamically
     * generated based on the idp code in the database.
     *
     * @param $c
     * @param $idpCode
     */
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

    /**
     * Get the settings information for this object.
     *
     * @return mixed
     */
    public function getSettings()
    {
        return $this->meta;
    }
}
