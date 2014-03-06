<?php
/**
 * Connects with ID.ME for VERIFICATION of MILITARY PERSONELL
 * 
 * @copyright  Copyright (c) 2014 E-Moxie Data Solutions, Inc.
 * @author     Matt Pramschufer <matt@emoxie.com>

 * @version    Release: 1.0
 * @since      Class available since Release 1.0
 * 
 * Example:
 * $idme = new IDME();
 * $idme->verify($_GET['code']);
 * returns true/false
 * 
 * echo $idme->displayButton();     

 */
class IDME {

    protected $SandboxMode = true;
    protected $AccessTokenURISandbox =          'https://api.sandbox.id.me/oauth/token';
    protected $AccessTokenURIProduction =       'https://api.id.me/oauth/token';
    protected $MilitaryProfileURISandbox =      'https://api.sandbox.id.me/v2/military.json';
    protected $MilitaryProfileURIProduction =   'https://api.id.me/v2/military.json';
    protected $AuthorizationURISandbox =        'https://api.sandbox.id.me/oauth/authorize';
    protected $AuthorizationURIProduction =     'https://api.id.me/oauth/authorize';
    protected $ClientId =                       '';
    protected $ClientSecret =                   '';
    protected $RedirectUri =                    '';
    protected $GrantType =                      'authorization_code';
    private $AccessToken;
    private $Code;

    
    /**
     * function verify()
     * Take the id.me generated code and passes it to 
     * $this->getAccessToken()
     * @param type $code
     */
    
    public function verify($code) {
        $this->Code = $code;
        $this->getAccessToken();
    }

    /**
     * function getAccessToken()
     * Takes the ID.ME supplied code and converts it into an oAuth Token
     * Passes token off to $this->getMilitaryProfile()
     * Token is only valid for 5 minutes
     * 
     * If error occurs, it forces function to return false
     * 
     */
    public function getAccessToken() {
        if ($this->SandboxMode) {
            $post_url = $this->AccessTokenURISandbox;
        } else {
            $post_url = $this->AccessTokenURIProduction;
        }

        $fields = array(
            'code' => $this->Code,
            'client_id' => $this->ClientId,
            'client_secret' => $this->ClientSecret,
            'redirect_uri' => $this->RedirectUri,
            'grant_type' => $this->GrantType
        );

        $string = http_build_query($fields);
        $results = $this->cURL($post_url, $string);

        if (isset($results['access_token'])) {
            $this->AccessToken = $results['access_token'];
            $this->getMilitaryProfile();
        } else {
            return false;
        }
    }

    
    /**
     * function getMiliaryProfile()
     * Takes the oAuth token and and grabs additonal data from ID.ME
     * Available results are
     * id, string
     * verified, boolean
     * affiliation, string
     * @return boolean
     */
    public function getMilitaryProfile() {

        if ($this->SandboxMode) {
            $post_url = $this->MilitaryProfileURISandbox;
        } else {
            $post_url = $this->MilitaryProfileURIProduction;
        }

        $post_url = $post_url . '?access_token=' . $this->AccessToken;
        $results = $this->cURL($post_url);
        
        return isset($results['verified']) && $results['verified'];
    }

    
    /**
     * function curl()
     * Takes supplied variables and does standard cURL function
     * @param type $post_url
     * @param type $post_fields
     * @return type
     */
    
    
    public function cURL($post_url, $post_fields){
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        if(!empty($post_fields)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        $response = curl_exec($ch);
        $results = json_decode($response, true);        
        
        return $results;
    }    
    
    /**
     * 
     * function displayButton()
     * Generates a button to display on frontend of website based on RedirectUri
     * 
     * usage: echo $idme->displayButton();
     * @param type $alt
     * @param type $src
     * @return string
     */    
    public function displayButton($alt, $src) {
        if ($this->SandboxMode) {
            $url = $this->AuthorizationURISandbox;
        } else {
            $url = $this->AuthorizationURIProduction;
        }

        $fields = array(
            'client_id' => $this->ClientId,
            'redirect_uri' => $this->RedirectUri,
            'response_type' => 'code',
            'scope' => 'military'
        );

        $combine = http_build_query($fields);

        $url = $url . '?' . $combine;

        $return = '<a href="' . $url . '">';
        $return .= '<img alt="' . $alt . '" src="' . $src . '" />';

        return $return;
    }

}