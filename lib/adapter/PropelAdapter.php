<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PropelAdapter extends sfAdapterBase
{

  public function run($data)
  {
    $result = new stdClass();

    $data_array = $data->toArray();

    foreach ($data_array as $key => $value)
    {
      $result->$key = $value;
    }

    return $result;

  }
}
