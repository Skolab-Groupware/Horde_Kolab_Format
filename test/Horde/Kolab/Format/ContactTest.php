<?php
/**
 * Test the contact XML format.
 *
 * $Horde: framework/Kolab_Format/test/Horde/Kolab/Format/ContactTest.php,v 1.4 2009/01/06 17:49:23 jan Exp $
 *
 * @package Kolab_Format
 */

/**
 * The Autoloader allows us to omit "require/include" statements.
 */
require_once 'Horde/Autoloader.php';


class DummyRegistry {
    function get()
    {
        return 'horde';
    }
}

class Horde_Kolab_Format_Xml_Contact_dummy extends Horde_Kolab_Format_Xml_Contact
{
    function _saveCreationDate($parent_node, $name, $value, $missing)
    {
        // Only create the creation date if it has not been set before
        if ($missing) {
            $value = 0;
        }
        return $this->_saveDefault($parent_node,
                                   $name,
                                   $value,
                                   array('type' => self::TYPE_DATETIME));
    }

    function _saveModificationDate($parent_node, $name, $value, $missing)
    {
        // Always store now as modification date
        return $this->_saveDefault($parent_node,
                                   $name,
                                   0,
                                   array('type' => self::TYPE_DATETIME));
    }
}

/**
 * Test the contact XML format.
 *
 * $Horde: framework/Kolab_Format/test/Horde/Kolab/Format/ContactTest.php,v 1.4 2009/01/06 17:49:23 jan Exp $
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Gunnar Wrobel <wrobel@pardus.de>
 * @package Kolab_Format
 */
class Horde_Kolab_Format_ContactTest extends PHPUnit_Framework_TestCase
{

    /**
     * Set up testing.
     */
    protected function setUp()
    {
        Horde_Nls::setCharset('utf-8');
    }

    /**
     * Test storing single mail addresses.
     */
    public function testSingleEmail()
    {
        $contact = &new Horde_Kolab_Format_Xml_contact_dummy();
        $object = array('uid' => '1',
                        'full-name' => 'User Name',
                        'email' => 'user@example.org');
        $xml = $contact->save($object);
        $expect = file_get_contents(dirname(__FILE__) . '/fixtures/contact_mail.xml');
        $this->assertEquals($expect, $xml);
    }

    /**
     * Test storing PGP public keys.
     */
    public function testPGP()
    {
        $contact = &new Horde_Kolab_Format_Xml_contact_dummy();
        $object = array('uid' => '1',
                        'full-name' => 'User Name',
                        'pgp-publickey' => 'PGP Test Key',
                        'email' => 'user@example.org');
        $xml = $contact->save($object);
        $expect = file_get_contents(dirname(__FILE__) . '/fixtures/contact_pgp.xml');
        $this->assertEquals($expect, $xml);
    }

    /**
     * Test loading a contact with a category.
     */
    public function testCategories()
    {
        global $prefs;

        $contact = &new Horde_Kolab_Format_Xml_contact();
        $xml = file_get_contents(dirname(__FILE__) . '/fixtures/contact_category.xml');
        $object = $contact->load($xml);
        $this->assertContains('Test', $object['categories']);

        $prefs = 'some string';
        $object = $contact->load($xml);
        $this->assertContains('Test', $object['categories']);
    }

    /**
     * Test loading a contact with a category with preferences.
     */
    public function testCategoriesWithPrefs()
    {
        @include_once 'Horde.php';
        @include_once 'Horde/Prefs.php';

        global $registry, $prefs;

        if (class_exists('Prefs')) {
            $registry = new DummyRegistry();
            $prefs = Prefs::singleton('session');
            /* Monkey patch to allw the value to be set. */
            $prefs->_prefs['categories'] = array('v' => '');
            
            $contact = &new Horde_Kolab_Format_Xml_contact();
            $xml = file_get_contents(dirname(__FILE__) . '/fixtures/contact_category.xml');

            $object = $contact->load($xml);
            $this->assertContains('Test', $object['categories']);
            $this->assertEquals('Test', $prefs->getValue('categories'));
        }
    }


}
