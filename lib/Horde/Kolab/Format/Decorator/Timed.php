<?php
/**
 * Determines how much time is spent while loading/saving the Kolab objects.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_Format
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Format
 */

/**
 * Determines how much time is spent while loading/saving the Kolab objects.
 *
 * Copyright 2010-2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_Format
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Format
 */
class Horde_Kolab_Format_Decorator_Timed
extends Horde_Kolab_Format_Decorator_Base
{
    /**
     * The timer used for recording the amount of time spent.
     *
     * @var Horde_Support_Timer
     */
    private $_timer;

    /**
     * An optional logger.
     *
     * @var mixed
     */
    private $_logger;

    /**
     * Time spent handling objects.
     *
     * @var float
     */
    static private $_spent = 0.0;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_Format  $handler The handler to be decorated.
     * @param Horde_Support_Timer $timer   The timer.
     * @param mixed               $logger  The optional logger. If set this
     *                                     needs to provide a debug() method.
     */
    public function __construct(
        Horde_Kolab_Format $handler,
        Horde_Support_Timer $timer,
        $logger = null
    ) {
        parent::__construct($handler);
        $this->_timer = $timer;
        $this->_logger = $logger;
    }

    /**
     * Load an object based on the given XML string.
     *
     * @param string &$xmltext The XML of the message as string.
     *
     * @return array The data array representing the object.
     *
     * @throws Horde_Kolab_Format_Exception
     */
    public function load(&$xmltext)
    {
        $this->_timer->push();
        $result = $this->getHandler()->load($xmltext);
        $spent = $this->_timer->pop();
        if (is_object($this->_logger)) {
            $this->_logger->debug(sprintf('Kolab Format data parsing complete. Time spent: %s ms', floor($spent * 1000)));
        }
        self::$_spent += $spent;
        return $result;
    }

    /**
     * Convert the data to a XML string.
     *
     * @param array &$object The data array representing the note.
     *
     * @return string The data as XML string.
     *
     * @throws Horde_Kolab_Format_Exception
     */
    public function save($object)
    {
        $this->_timer->push();
        $result = $this->getHandler()->save($object);
        $spent = $this->_timer->pop();
        if (is_object($this->_logger)) {
            $this->_logger->debug(sprintf('Kolab Format data generation complete. Time spent: %s ms', floor($spent * 1000)));
        }
        self::$_spent += $spent;
        return $result;
    }

    /**
     * Report the time spent for loading/saving objects.
     *
     * @return float The amount of time.
     */
    public function timeSpent()
    {
        return self::$_spent;
    }
}