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
 * @category  PRS

 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */

require_once 'DAT.php';

/**
 * A REST Parameter
 *
 * @category  PRS
 * @package   PLOR
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2010 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class PRSParameters extends DAT
{ 
    protected static $instance;

    /**
     * Une seule instance
     *
     * @return PRSParameters
     */
    static public function singleton($encoding = 'UTF-8')
    {
        if (!isset(self::$instance)) {
            self::$instance = new PRSParameters;
            self::$instance->fixEncoding($encoding);

            // headers parameters
            self::$instance->headers = new stdClass;
            if (isset($_SERVER['CONTENT_LENGTH'])) 
                self::$instance->headers->content_length = $_SERVER['CONTENT_LENGTH'];
            if (isset($_SERVER['CONTENT_TYPE'])) 
                self::$instance->headers->content_type = new PSO($_SERVER['CONTENT_TYPE']);

            foreach($_SERVER as $k => $v) {
                if (substr($k, 0, 5) == 'HTTP_') {
                    $k = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($k, 5))));
                    self::$instance->headers->$k = PSO::factory($v)->fixEncoding($encoding);
                }
            }
            foreach($_REQUEST as $k => $v) {

                if (is_string($v)) {
                    $k = self::$instance->normalizeKey($k);
                    self::$instance->{$k} = PSO::factory($v)->fixEncoding($encoding);
                }
            }
        }

        return self::$instance;
    }

}
