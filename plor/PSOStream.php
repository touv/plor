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

require_once 'PSO.php';

/**
 * a PSO stream wrapper  in PHP
 *
 * @category  PSO
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PSOStream
{
    public static $handles = array();
    protected $varname;
    protected $position = 0;
    protected $readable = true;
    protected $writeble = true;

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->varname = parse_url($path, PHP_URL_HOST);

        if (is_null($this->varname)) return false;


        if (!isset(self::$handles[$this->varname])) {
            self::$handles[$this->varname] = new PSO;
        }

        if ($mode[0] === 'r') {
            $this->readable = true;
            $this->writeble = strpos($mode, '+') === false ? false : true;
            $this->position = 0;
        }
        elseif ($mode[0] === 'w') {
            $this->readable = strpos($mode, '+') === false ? false : true;
            $this->writeble = true;
            self::$handles[$this->varname]->exchange(null);
            $this->position = 0;
        }
        elseif ($mode[0] === 'a') {
            $this->readable = strpos($mode, '+') === false ? false : true;
            $this->writeble = true;
            $this->position = count(self::$handles[$this->varname]);
        }

        return true;
    }

    function stream_read($count)
    {
        if (!$this->readable) return false;
        $ret = self::$handles[$this->varname]->substr($this->position, $count);
        $this->position += count($ret);
        return $ret;
    }

    function stream_write($data)
    {
        if (!$this->writeble) return false;
        $size = strlen($data);
        self::$handles[$this->varname]->exchange(
            self::$handles[$this->varname]
            ->substr(0, $this->position)
            ->concat($data)
            ->concat(self::$handles[$this->varname]->substr($this->position + $size))
            ->toString());
        $this->position += $size;
        return $size;
    }

    function stream_tell()
    {
        return $this->position;
    }

    function stream_eof()
    {
         return $this->position >= count(self::$handles[$this->varname]);
    }

    function stream_seek($offset, $whence)
    {
        $size = count(self::$handles[$this->varname]);
        if ($whence === SEEK_SET and $offset < $size and $offset >= 0) {
            $this->position = $offset;
            return true;
        }
        elseif($whence === SEEK_CUR and $offset >= 0) {
            $this->position += $offset;
            return true;
        }
        elseif($whence === SEEK_END and strlen($size + $offset >= 0)) {
            $this->position = $size + $offset;
            return true;
        }
        else {
            return false;
        }
    }
}

stream_wrapper_register('pso', 'PSOStream');
