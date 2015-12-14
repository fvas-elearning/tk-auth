<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Auth\Adapter;
use Tk\Auth\Result;

/**
 * A authenticator adaptor
 *
 * NOTICE: This is only to be enabled for test sites, not to be used in production.
 *
 * To be used in conjunction with the tk-tools commands.
 *
 * Config options:
 *
 *   $config['system.auth.trapdoor.key']    = '{Some unique key}'
 *
 * @see tk-tools
 */
class Trapdoor extends Iface
{

    protected $masterKey = '';

    /**
     * Constructor
     *
     * @param  string $username The username of the account being authenticated
     * @param  string $password The password of the account being authenticated
     * @param array $config
     */
    public function __construct($username = null, $password = null, $config = array())
    {
        parent::__construct($username, $password);
        // Generate the masterkey

        $key = !empty($config['system.auth.trapdoor.key']) ? $config['system.auth.trapdoor.key'] : '';
        if (!$key) {
            $tz = ini_get('date.timezone');
            ini_set('date.timezone', 'Australia/Victoria');
            $key = date('=d-m-Y=', time()); // Changes daily
            ini_set('date.timezone', $tz);
        }
        
        $this->masterKey = \Tk\Auth::hash($key, 'md5');
        
    }

    /**
     * authenticate() - defined by Tk_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @return Result
     */
    public function authenticate()
    {
        // Authenticate against the masterKey
        if (strlen($this->getPassword()) >= 32 && $this->masterKey) {
            if ($this->masterKey == $this->getPassword()) {
                return new Result(Result::SUCCESS, $this->getUsername());
            }
        }
        return new Result(Result::FAILURE, $this->getUsername(), 'Invalid username or password.');
    }


}
