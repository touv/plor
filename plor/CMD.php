<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker encoding=utf8:
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
 * @category  CMD
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
require_once 'PSO.php';

/**
 * a shell facade in PHP
 *
 * @category CMD 
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class CMD
{
    const NOHUP = 1;
    const OPT_EQUAL = 1;
    const OPT_MINUS = 2;
    const OPT_QUOTE = 4;
    protected $command;
    protected $redirect = array(0 => '/dev/null', 2 => '/dev/null');
    protected $mode;
    protected $pid;
    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($command, $mode = null)
    {
        $this->exchange($command, $mode);
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return CMD
     */
    public static function factory($command = '', $mode = null)
    {
        return new CMD($command, $mode);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return CMD
     */
    public function exchange($command, $mode = null) 
    {
        if (!is_string($command))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($command).' given', E_USER_ERROR);
        if (!is_null($mode) and !is_integer($mode)) 
            trigger_error('Argument 2 passed to '.__METHOD__.' must be a integer, '.gettype($mode).' given', E_USER_ERROR);

        $this->command = $command;
        $this->mode = $mode;
        return $this;
    }

    /**
     * Add option
     * @return CMD
     */
    public function option($name, $value = null, $mode = null)
    {
        if (!is_string($name))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($name).' given', E_USER_ERROR);
        if (!preg_match(',\w+,i', $name))
            trigger_error('Argument 1 passed to '.__METHOD__.' must contain word\'s characters, '.$name.' given', E_USER_ERROR);
        if (!is_null($value) and !is_string($value)) 
            trigger_error('Argument 2 passed to '.__METHOD__.' must be a string, '.gettype($value).' given', E_USER_ERROR);
        if (!is_null($mode) and !is_integer($mode)) 
            trigger_error('Argument 3 passed to '.__METHOD__.' must be a integer, '.gettype($mode).' given', E_USER_ERROR);
        $min = strlen($name) == 1 ? '-' : '--';
        if (!is_null($mode) and ($mode & self::OPT_MINUS == self::OPT_MINUS))
            $min = '-';
        $this->command .= ' '.$min.$name;
        if (!is_null($value)) {
            $sep = ((strlen($min) == 1) or ($mode & self::OPT_EQUAL != self::OPT_EQUAL)) ? ' ' : '=';
            $this->command .= $sep.escapeshellarg($value);
        }
        return $this;
    }

    /**
     * Add param
     * @return CMD
     */
    public function param($value)
    {
        if (!is_string($value))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($value).' given', E_USER_ERROR);

        $this->command .= ' '.escapeshellcmd($value);
        return $this;
    }

     /**
     * Add param
     * @return CMD
     */
    public function bind($handle, PFO $stream)
    {
        $redirect[$handle] = '/dev/null';
        return $this;
    }

    /**
     * execute command
     * @return CMD
     */
    public function isAlive()
    {
        if (!is_null($this->pid) and posix_kill($this->pid, 0)) 
            return true;
        else 
            return false;
    }


    /**
     * execute command
     * @return CMD
     */
    public function fire()
    {
        $compl = '';
        if (isset($this->redirect[0])) $compl .= ' < '.$this->redirect[0];
        if (isset($this->redirect[1])) $compl .= ' > '.$this->redirect[1];
        if (isset($this->redirect[2])) $compl .= ' 2> '.$this->redirect[2];
        if (!is_null($this->mode) and ($this->mode & self::NOHUP == self::NOHUP)) {
            if(!isset($this->redirect[1])) $compl .= ' > /dev/null';
            $this->command = 'nohup '.$this->command.' '.$compl.'& echo $!';
            $this->pid = shell_exec($this->command);
            return PSO::factory($this->pid);
        }
        elseif(!isset($this->redirect[1])) {
            $this->pid = null;
            return PSO::factory(shell_exec($this->command))->rtrim();
        }
    }
}
