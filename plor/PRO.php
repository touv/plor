<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :
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
 * @category  PSO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

require_once 'PROItem.php';
/**
 * a reader object
 *
 * @category  PRO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PRO
{
    protected $stack;
    protected $types;
    protected $names;
    protected $depth = 0;
    protected $position = 0;
    protected $allowed_types = array('stdClass', 'array');

    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($content = null, $allowed_types = null)
    {
        $this->exchange($content, $allowed_types);
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return PSO
     */
    public static function factory($content = null, $allowed_types = null)
    {
        return new PRO($content, $allowed_types);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return PSO
     */
    public function exchange($content = null, $allowed_types = null) 
    {
        $this->stack = array($content);
        $this->types = array($this->getcase($content));
        $this->names = array();
        $this->depth = 0;
        $this->position = 0;
        if (is_array($allowed_types))
            $this->allowed_types = $allowed_types;
        return $this;
    }

    private function getcase(&$o) 
    {
        if (is_object($o)) {
            return get_class($o);
        }
        elseif (is_array($o)) {
            return 'array';
        }
        else {
            return null;
        }
    }

    /**
     * Retourne une portion de chaine
     *
     * @return object
     */
    public function fetch()
    {
        $cur = current($this->stack[$this->depth]);
        if ($cur === false and $this->depth == 0) {
            $this->position = 0;
            return false;
        }
        elseif ($cur === false and $this->depth > 0) {
            --$this->depth;
            return $this->fetch();
        }
        $r = new PROItem;
        $r->name = key($this->stack[$this->depth]); 
        $r->baseURI = '/'.implode($this->names, '/');
        $r->uri   = rtrim($r->baseURI, '/').'/'.$r->name;
        $r->value = $cur; 
        $r->index = $this->position++;
        $r->type  = $this->getcase($cur);
        $r->depth = $this->depth;

        next($this->stack[$this->depth]);
        if ($r->type === $this->types[$this->depth] or in_array($r->type, $this->allowed_types)) {
            ++$this->depth;
            $this->stack[$this->depth] =& $cur;
            reset($this->stack[$this->depth]);
            $this->types[$this->depth] = $r->type;
            $this->names[$this->depth] = $r->name;
            return $this->fetch();
        }
        return $r;
    }

    /**
     * Retourne toute les lignes du résulat de la requete 
     *
     * @return ArrayObject
     */
    public function fetchAll()
    {
        $ret =  new ArrayObject();
        while($row = $this->fetch()) 
            $ret->append($row);
        return $ret;
    }

}
