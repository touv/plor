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
 * @category  CHN
 * @package   P3C
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */


/**
 * a chain object in PHP
 *
 * @category CHN 
 * @package   P3C
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class CHN implements Countable, Iterator, ArrayAccess 
{
    protected $content = array();

    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($content = null)
    {
        $this->exchange($content);
    }
    /**
     * Factory
     * @param string 
     * @param string
     * @return CHN
     */
    public static function factory($content = null)
    {
        return new CHN($content);
    }
    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return STR
     */
    public function exchange($content = null) 
    {
        if (is_array($content)) foreach($content as $k => $v) {
            $this->offsetSet($k, $v);
        }
    }
    /**
     * invoking inaccessible methods in an object context.
     *  @param string
     *  @param array
     * @return CHN
     */
    public function __call($method, $args) 
    {
        foreach($this->content as $item) {
            if (method_exists($item, $method)) {
                call_user_func_array(array($item, $method), $args);
            }
        }
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
        if (!is_object($value)) return false;

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
}
