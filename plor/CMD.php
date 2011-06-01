<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker:
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
require_once 'Fetchor.php';
require_once 'Encoding.php';
require_once 'Dumpable.php';
require_once 'PSO.php';
require_once 'DAT.php';

/**
 * a shell facade in PHP
 *
 * @category CMD 
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class CMD implements Fetchor, Dumpable, Encoding
{
    protected $__encoding = 'UTF-8';

    const STDIN  = 0;
    const STSOUT = 1;
    const STDERR = 2;
    const OPT_EQUAL = 1;
    const OPT_MINUS = 2;
    const OPT_QUOTE = 4;

    public static $buffersize = 8192;

    protected $options = array(
        'short_option_separator' => '-',
        'short_option_operator'  => ' ',
        'long_option_separator'  => '--',
        'long_option_operator'   => '=',
        'short_option_size'      => 1,
    );
    protected $ending = "\n";

    protected $command;
    protected $process;
    protected $descriptorspec = array(1 => array('pipe','w'));
    protected $descriptors = array();
    protected $cwd;
    protected $env;


    /**
     * Constructor
     * @param string 
     * @param string
     */
    public function __construct($command, $options= null)
    {
        $this->exchange($command, $options);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Factory
     * @param string 
     * @param string
     * @return CMD
     */
    public static function factory($command = '', $options = null)
    {
        return new CMD($command, $options);
    }

    /**
     * Exchange
     *
     * @param string 
     * @param string
     * @return CMD
     */
    public function exchange($command, $options = null) 
    {
        if (!is_string($command))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($command).' given', E_USER_ERROR);
        if (!is_null($options) and !is_array($options)) 
            trigger_error('Argument 2 passed to '.__METHOD__.' must be an array, '.gettype($options).' given', E_USER_ERROR);

        $this->command = $command;
        if (!is_null($options))
            $this->options = array_merge($this->options, $options);
        return $this;
    }


    /**
     * set string encoding
     * @param string
     * @return CMD
     */
    public function fixEncoding($e)
    {
        if (!is_string($e))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($e).' given', E_USER_ERROR);
        $this->__encoding = $e;
        return $this;
    }


    /**
     * Use the class as string
     * @return string
     */
    public function __toString()
    {
        return (string)$this->command;
    }

    /**
     * Convert class to string
     * @return string
     */
    public function toString()
    {
        return (string)$this->command;
    }

    /**
     * Dump content of the class
     * @return CMD
     */
    public function dump($s = null)
    {
        echo $this->toString(), $s;
        return $this;
    }

    /**
     * Add option
     * @return CMD
     */
    public function option($name, $value = null)
    {
        if (!is_string($name))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a string, '.gettype($name).' given', E_USER_ERROR);
        if (!preg_match(',\w+,i', $name))
            trigger_error('Argument 1 passed to '.__METHOD__.' must contain word\'s characters, '.$name.' given', E_USER_ERROR);
        if (!is_null($value) and !is_string($value)) 
            trigger_error('Argument 2 passed to '.__METHOD__.' must be a string, '.gettype($value).' given', E_USER_ERROR);

        $min = strlen($name) == $this->options['short_option_size'] ? $this->options['short_option_separator'] : $this->options['long_option_separator'];
        $this->command .= ' '.$min.$name;
        if (!is_null($value)) {
            $sep = strlen($name) == $this->options['short_option_size'] ? $this->options['short_option_operator'] : $this->options['long_option_operator'];
            if ($value == '')
                $this->command .= $sep.'\'\'';
            else
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
    public function bind($desc, $url)
    {
        if (!is_integer($desc))
            trigger_error('Argument 1 passed to '.__METHOD__.' must be a integer, '.gettype($desc).' given', E_USER_ERROR);
        if (!is_string($url))
            trigger_error('Argument 2 passed to '.__METHOD__.' must be a string, '.gettype($url).' given', E_USER_ERROR);
        $turl = parse_url($url);
        $name = isset($turl['path']) ? realpath($turl['path']) : null;
        $mode = $desc == 0 ? 'r' : 'w';

        // Comment savoir sur le flux est compatible avec proc_open ?
        if ($name) {
            $this->descriptorspec[$desc] = array('file', $name, $mode);
        }
        else {
            $this->descriptorspec[$desc] = array('pipe' , $mode);
            $this->descriptors[$desc] = fopen($url, $mode);
        }
        return $this;
    }

    /**
     * execute command
     * @return CMD
     */
    public function fire()
    {
        $this->process = proc_open($this->command, $this->descriptorspec, $this->pipes, $this->cwd, $this->env);

        foreach($this->descriptors as $k => $descriptor) 
            if (isset($this->descriptors[$k]) and isset($this->pipes[$k]) and isset($this->descriptorspec[$k])) {
                if ($k == 0) {
                    $src = $this->descriptors[$k];
                    $dst = $this->pipes[$k];
                }
                else {
                    $src = $this->pipes[$k];
                    $dst = $this->descriptors[$k];
                }
                while (!feof($src)) {
                    fwrite($dst,fread($src, self::$buffersize));
                }
                fclose($src);
                fclose($dst);
                $this->descriptors[$k] = null;
            }

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
     * Retourne une ligne du résulat de la commande
     *
     * @return PSO
     */
    public function fetch()
    {
        if (!$this->process and !is_resource($this->pipes[1])) return false;

        if (feof($this->pipes[1])) {
            $this->close();
            return false;
        }
        $s = stream_get_line($this->pipes[1], self::$buffersize, $this->ending);
        if ($s === false) {
            $this->close();
            return false;
        }
        return PSO::factory($s)->fixEncoding($this->__encoding);
    }

    /**
     * Retourne toute les lignes du résulat de la commande
     *
     * @return PSO
     */
    public function fetchAll()
    {
        $ret = new DAT;
        while($row = $this->fetch()) 
            $ret->append($row);
        return $ret;
    }

    /**
     * Ferme ce qui est ouvert
     *
     * @return PQO
     */
    public function close()
    {
        foreach($this->pipes as $h)
            if (is_resource($h)) fclose($h);
        foreach($this->descriptors as $h)
            if (is_resource($h)) fclose($h);

        $this->pipes = array();
        if ($this->process)
            $return_value = proc_close($this->process);

        $this->process = null;

        return $this;
    }

    /**
     * arrete la commande
     *
     * @return PQO
     */
    public function stop()
    {
        if ($this->process) {
            proc_terminate($this->process);
        }
        return $this;
    }

    /**
     * test sie le process est en vie
     *
     * @return boolean
     */
    public function isAlive()
    {
        if (!$this->process) return false;
        $infos = proc_get_status($this->process);
        return $infos['running'];
    }

}
