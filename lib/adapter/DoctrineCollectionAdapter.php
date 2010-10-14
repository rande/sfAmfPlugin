<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class DoctrineCollectionAdapter extends sfAdapterBase
{

  public static function getInstance()
  {
    return new DoctrineCollectionAdapter();
  }

  public function run($data)
  {
    $result = array();
    $size = sizeof($data);
    $record_adapter = new DoctrineRecordAdapter();

    for ($i = 0; $i < $size; $i++)
    {
      $result[$i] = $record_adapter->run($data[$i]);
    }

    return $result;
  }
}
