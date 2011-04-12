<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker encoding=utf8 :
/**
 * P3C
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
 * @category  PRS
 * @package   P3C
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

require_once 'STR.php';
require_once 'CHN.php';
require_once 'PRSHeaders.php';
require_once 'PRSInput.php';

/**
 * a simple REST Server in PHP
 *
 * @category  PRS
 * @package   P3C
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PRS implements Countable, Iterator, ArrayAccess
{
    protected $options = array(
        'ignore_user_abort' => true,
        'time_limit'        => 0,
        'error_reporting'   => E_ALL,
        'implicit_flush'    => true,
        'powered_by'        => 'PRS/1.0-php',
        'compatible'        => false,
        'base'              => '',
    );
    protected $content = array();

    protected $input;

    /**
     * Constructor
     * @param array
     */
    public function __construct($content = array())
    {
        $this->exchange($content);
    }

    /**
     * Factory
     * @param string 
     * @return PRSUrl
     */
    public static function factory($content = array())
    {
        return new PRS($content);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return STR
     */
    public function exchange($content = array()) 
    {
        if (!is_array($content))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a array, '.gettype($content).' given', E_USER_ERROR);

        $this->options = array_merge($this->options, $content);
        ignore_user_abort($this->options['ignore_user_abort']);
        set_time_limit($this->options['time_limit']);
        error_reporting($this->options['error_reporting']);
        ob_implicit_flush($this->options['implicit_flush']);
        $_SERVER['REQUEST_BASE'] = $this->options['base'];
        if ($this->options['compatible'] and isset($_GET['_'])) {
            $_SERVER['REQUEST_METHOD'] = strtoupper($_GET['_']);
        }
        $this->input = new PRSInput;
        $this->content = array();
        return $this;
    }

    /**
     *  @see Iterator
     */
    public function rewind() 
    {
        reset($this->content);
    }
    /**
     *  @see Iterator
     */
    public function current() 
    {
        return current($this->content);
    }
    /**
     *  @see Iterator
     */
    public function key() 
    {
        return key($this->content);
    }
    /**
     *  @see Iterator
     */
    public function next() 
    {
        return next($this->content);
    }
    /**
     *  @see Iterator
     */
    public function valid() 
    {
        return $this->current() !== false;
    }
    /**
     *  @see ArrayAccess
     */
    public function offsetExists($offset) 
    {
        return isset($this->content[$offset]);
    }
    /**
     *  @see ArrayAccess
     */
    public function offsetGet($offset) 
    {
        return $this->content[$offset];
    }
    /**
     *  @see ArrayAccess
     */
    public function offsetSet($offset, $value) 
    {
        if (! $value instanceof PRSUrl) 
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a PRSUrl, '.gettype($value).' given', E_USER_ERROR);

        $value->setInput($this->input);
        if (is_null($offset))
            array_push($this->content, $value);
        else 
            $this->content[$offset] = $value;
        return true;
    }
    /**
     *  @see ArrayAccess
     */
    public function offsetUnset($offset) 
    {
        unset($this->content[$offset]);
    }
    /**
     *  @see Countable
     */
    public function count() 
    {
        return sizeof($this->content);
    }

    /**
     * Register a PRSUrl
     * @return PRS
     */
    public function register(PRSUrl $url)
    {
        $this->offsetSet(null, $url);
        return $this;
    }

    /**
     *  Launch Server
     * 
     */
    public function listen(PRSHeaders $headers = null)
    {
        if (is_null($headers)) {
            $headers = new PRSHeaders($this->options['powered_by']);
        }
        $found = null;
        foreach($this->content as $url) {
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
