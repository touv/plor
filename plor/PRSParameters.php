<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker encoding=utf8 :
/**
 * PLOR
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

 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

/**
 * A REST Parameter
 *
 * @category  PRS
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PRSParameters implements ArrayAccess 
{ 
    public static $encoding = 'UTF-8';
    protected static $instance;
    protected $parameters = array();
    protected $content = array();

    /**
     * Une seule instance
     *
     * @return PRSParameters
     */
    static public function singleton()
    {
        if (!isset(self::$instance)) {
            self::$instance = new PRSParameters;
        }
        return self::$instance;
    }

    /**
     * exchange
     * 
     * @param array
     * @return mixed
     */
    public function exchange(array $parameters) 
    {
        foreach($parameters as $p) {
            if (is_array($p)) {
                foreach($p as $q)
                    if (is_string($q)) 
                        $this->parameters[$q] = $p;
            }
            else {
                $this->parameters[$p] = array($p);
            }
        }
    }

    /**
     * @see Magic
     */
    public function __get($offset) 
    {
        return $this->offsetGet($offset);
    }
    /**
     * @see Magic
     */
    public function __set($offset, $value) 
    {
        return $this->offsetSet($offset, $value);
    }
    /**
     * @see Magic
     */
    public function __isset($offset) 
    {
        return $this->offsetExists($offset);
    }
    /**
     * @see Magic
     */
    public function __unset($offset) 
    {
        return $this->offsetUnset($offset);
    }
    /**
     *  @see ArrayAccess
     */
    public function offsetExists($offset) 
    {
        return isset($this->parameters[$offset]) or isset($this->content[$offset]);
    }
    /**
     *  @see ArrayAccess
     */
    public function offsetGet($offset) 
    {
        if (isset($this->parameters[$offset])) {
            foreach($this->parameters[$offset] as $p) {
                if (is_string($p) and isset($_REQUEST[$p])) {
                    return (is_string($_REQUEST[$p]) ? PSO::factory($_REQUEST[$p], self::$encoding) : $_REQUEST[$p]);
                }
            }
        }
        elseif (isset($this->content[$offset])) {
            return (is_string($this->content[$offset]) ? PSO::factory($this->content[$offset], self::$encoding) : $this->content[$offset]);
        }
        return null;
    }
    /**
     *  @see ArrayAccess
     */
    public function offsetSet($offset, $value) 
    {
        if (isset($this->parameters[$offset])) {
            $_REQUEST[$this->parameters[$offset][0]] = $value;            
        }
        else {
            if (is_null($offset)) {
                array_push($this->content, $value);
            }
            else {
                $this->content[$offset] = $value;
            }
        }
        return true;
    }
    /**
     *  @see ArrayAccess
     */
    public function offsetUnset($offset) 
    {
        unset($this->content[$offset]);
    }

}
