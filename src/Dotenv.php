<?php

namespace Nexus633\Dotenv;

use Nexus633\Dotenv\Exception\FileNotFoundException;

class Dotenv extends ParseEnvironmentFile
{
    /**
     * Option to add .env to global $_ENV and/or $_SERVER
     *
     * default
     * <code>
     *    $options = [
     *        'set_locale_first' = true,
     *        'set_exceptions' = true,
     *        'set_global_env' = false,
     *        'set_global_server' = false,
     *        'set_global_key' = false
     *     ];
     * </code>
     *
     *  If the <code>'set_global_key'</code> option is set to true,<br>
     *  a key is created in the globals $_SERVERS and $_ENV<br>
     *  where the .env file is then initialized.
     *
     * @param array $options
     * @throws FileNotFoundException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * get environment key
     *
     * @param string $value
     * @param mixed $default
     * @return mixed
     */
    public function processenv(string $value = '', mixed $default = ''): mixed
    {
        if(empty($value) && empty($default)){
            return $this->getAll();
        }
        return $this->get($value) ?: $default;
    }
}
