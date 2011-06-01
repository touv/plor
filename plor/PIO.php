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

require_once 'Fetchor.php';
require_once 'Encoding.php';
require_once 'PSO.php';
require_once 'DAT.php';

/**
 * PIO is Input Object
 *
 * @category  PIO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PIO implements Fetchor, Encoding
{
    protected $__encoding = 'UTF-8';
    public static $buffersize = 8192;

    protected $ending = "\n";

    protected $content;
    protected $handle;

    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($content = '')
    {
        $this->exchange($content);
    }

    /**
     * __destruct
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return PSO
     */
    public static function factory($content = '')
    {
        return new PIO($content);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return PSO
     */
    public function exchange($content = '') 
    {
        if (is_null($content)) $content = ''; // Pas de valeur null
        if (!is_string($content))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($content).' given', E_USER_ERROR);
        $this->close();
        $this->content = $content;
        return $this;
    }

    /**
     * set string encoding
     * @param string
     * @return PIO
     */
    public function fixEncoding($e)
    {
        if (!is_string($e))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($e).' given', E_USER_ERROR);
        $this->__encoding = $e;
        return $this;
    }


    /**
     * Fixe le sépérateur de ligne
     *
     * @return object
     */
    public function setEnding($s)
    {
        if (!is_string($s))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($s).' given', E_USER_ERROR);
        $this->ending = $s;
        return $this;
    }


    /**
     * Retourne une portion du buffer
     *
     * @return object
     */
    public function fetch()
    {
        if (is_null($this->handle)) {
            $this->handle = @fopen($this->content, 'r');
        }
        if (!$this->handle or feof($this->handle)) {
            $this->close();
            return false;
        }
        $s = stream_get_line($this->handle, self::$buffersize, $this->ending);
        if ($s === false) {
            $this->close();
            return false;
        }
        return PSO::factory($s)->fixEncoding($this->__encoding);
    }

    /**
     * Retourne toute les lignes du résulat de la requete 
     *
     * @return DAT
     */
    public function fetchAll()
    {
        $ret = new DAT;
        while($row = $this->fetch()) 
            $ret->append($row);
        return $ret;
    }

    /**
     * Ferme le curseur courant 
     *
     * @return PQO
     */
    public function close()
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
        return $this;
    }


}
