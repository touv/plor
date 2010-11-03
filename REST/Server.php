<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker encoding=utf8 :
/**
 * REST_Server
 *
 * Copyright (c) 2010, Nicolas Thouvenin
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the author nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE REGENTS AND CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  REST
 * @package   REST_Client
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

require_once 'REST/Headers.php';
require_once 'REST/Input.php';

/**
 * a simple REST Server in PHP
 *
 * @category  REST
 * @package   REST_Server
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class REST_Server
{
    protected $options = array(
        'ignore_user_abort' => true,
        'time_limit'        => 0,
        'error_reporting'   => E_ALL,
        'implicit_flush'    => true,
        'powered_by'        => 'REST_Server/1.0-php',
        'compatible'        => false,
        'base'              => '',
    );
    protected $urls = array();

    protected $input;

    /**
     * Constructor
     * @param string
     * @param array
     */
    public function __construct($options = array())
    {
        $this->options = array_merge($this->options, $options);

        ignore_user_abort($this->options['ignore_user_abort']);
        set_time_limit($this->options['time_limit']);
        error_reporting($this->options['error_reporting']);
        ob_implicit_flush($this->options['implicit_flush']);
        $_SERVER['REQUEST_BASE'] = $this->options['base'];
        if ($this->options['compatible'] and isset($_GET['_'])) {
            $_SERVER['REQUEST_METHOD'] = strtoupper($_GET['_']);
        }

        $this->input = new REST_Input();
    }

    /**
     * REST_Server factory
     * @return REST_Server 
     */
    public static function factory($options = array())
    {
        return new REST_Server($options);
    }

    /**
     * Register a REST_Url
     * @return REST_Server 
     */
    public function register(REST_Url $url)
    {
        $url->setInput($this->input);
        $this->urls[] = $url;
        return $this;
    }

    /**
     *  Launch Server
     * 
     */
    public function listen()
    {
        $headers = new REST_Headers($this->options['powered_by']);
        $found = null;
        reset($this->urls);
        while (list(, $url) = each($this->urls)) {
            if ($url->check()) {
                $found = $url;
                break;
            }
        } 
        if (is_null($found)) {
            return $headers->send(404, true);
        }

        if (!$found->apply($headers)) {
            return $headers->send(405, true);
        }

        if (!$headers->getStatus()) {
            return $headers->send(500, true);
        }

        return $headers->getStatus();
    }


}
