<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class DoctrineRecordAdapter extends sfAdapterBase
{

  public function run($data)
  {
    $columns = $data->getTable()->getColumns();

    $relations = $data->getTable()->getRelations();

    $available_columns = $data->toArray();
    $result = new stdClass();

    foreach ($available_columns as $cn => $value)
    {
      if (!array_key_exists($cn, $columns))
      {

        if ($data->$cn instanceof Doctrine_Collection)
        {
          $result->$cn = DoctrineCollectionAdapter::getInstance()->run($data->$cn);
        }
        else
        {
          $result->$cn = $this->run($data->$cn);
        }
      }
      else if (array_key_exists($cn, $columns))
      {
        $cp = $columns[$cn];
        $to_type = 'to_' . $cp['type'];
        $result->$cn = $this->$to_type($data->$cn);
      }
    }

    return $result;

  }
}
