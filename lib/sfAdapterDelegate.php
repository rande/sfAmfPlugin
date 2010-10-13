<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008-2009 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Enter description here...
 *
 */
class sfAdapterDelegate {
    public static function getInstance() {
        return new sfAdapterDelegate();
    }
/**
 *
 *
 * @param unknown_type $data
 */
    public function convert($data) {

        if (is_object($data)) {
            if (class_exists('Doctrine_Record') && $data instanceof Doctrine_Record) {
                $adapter = new DoctrineRecordAdapter();
            }
            else if (class_exists('Doctrine_Collection') && $data instanceof Doctrine_Collection) {
                $adapter = new DoctrineCollectionAdapter();
            }
            else {
                $reflector = new ReflectionClass(get_class($data));
                $interfaces = $reflector->getInterfaces();
                if (array_key_exists("Persistent", $interfaces)) {
                    $adapter = new PropelAdapter();
                }
            }

            if (isset($adapter)) {
                return $adapter->run($data);
            }
            else {
                return $data;
            }
        }
        else if (is_array($data)) {
                return $this->iterateArray($data);
            }
            else {
                return $data;
            }
    }

    private function iterateArray($data) {
        $result = array();
        foreach($data as $key=>$item) {
            $result[$key] = $this->convert($item);
        }
        return $result;
    }
}
?>