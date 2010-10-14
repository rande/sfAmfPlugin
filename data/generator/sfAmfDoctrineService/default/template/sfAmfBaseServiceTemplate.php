[?php

/**
 * <?php echo $this->table->getClassnameToReturn() ?> service base class.
 *
 * @package    ##PROJECT_NAME##
 * @author     ##AUTHOR_NAME##
 */
abstract class Base<?php echo $this->table->getClassnameToReturn() ?>Service extends sfAmfService 
{
  /**
   * create a record in database from ValueObject
   *
   * @param ValueObject Remote object
   * @return boolean True if creation succeeds
   */   
  public function create($valueObject)
  {
    $baseObject = $this->fromValueObject($valueObject);
    try
    {
      $baseObject->save();
      return true;
    } 
    catch(Doctrine_Exception $e)
    {
      return false;
    }
  }
  
  /**
   * create a record in database from ValueObject
   *
   * @param int Offset of ValueObject to return  (default: 0)
   * @param int Limit of ValueObject to return, 0 being no limit (default: 0)
   *
   * @return Array of ValueObject
   * @AmfClassMapping(name="<?php echo $this->getFullVoPackage() ?>.<?php echo $this->table->getClassnameToReturn(); ?>ValueObject")
   * @AmfReturnType("ArrayCollection")
   */   
  public function retrieveAll($offset = 0, $limit = 10)
  {
    $valueObjects = array();

    $baseObjects = Doctrine_Core::getTable('<?php echo $this->table->getClassnameToReturn() ?>')
      ->createQuery()
      ->limit($limit)
      ->offset($offset)
      ->execute();

    foreach($baseObjects as $baseObject)
    {
      $valueObjects[] = $this->toValueObject($baseObject);
    } 

    return $valueObjects;
  }

  /**
   * get a record from database 
   *
   * @param int Object id
   * @return ValueObject Object from atabase record 
   * @AmfClassMapping(name="<?php echo $this->getFullVoPackage() ?>.<?php echo $this->table->getClassnameToReturn(); ?>ValueObject")
   */   
  public function retrieveById($id)
  {
    $baseObject = Doctrine_Core::getTable('<?php echo $this->table->getClassnameToReturn() ?>')->findOneById($id);
    return $this->toValueObject($baseObject);
  }

  /**
   * update a record in database from ValueObject
   *
   * @param ValueObject Remote object
   * @return boolean True if update succeeds
   */   
  public function update($valueObject)
  {
    return $this->create($valueObject);
  }

  /**
   * delete a record in database from ValueObject
   *
   * @param ValueObject Remote object
   * @return boolean True if delete succeeds
   */   
  public function delete($valueObject)
  {
    $this->fromValueObject($valueObject)->delete();
  }

  /**
   * get initialized BaseObject from ValueObject
   *
   * @param ValueObject Remote object
   * @return BaseObject The model object
   */   
  protected function fromValueObject($valueObject)
  {
    $baseObject = new <?php echo $this->table->getClassnameToReturn() ?>();
    
<?php
// set baseObject fields via setter based on valueObject fields
// PK is ignored as it need a custom action.
$pkColumn = '';
$columns = $this->table->getColumns();

foreach ($columns as $columnName => $column)
{
  if ((array_key_exists('primary', $column) === TRUE) && $column['primary'])
  {
    $pkColumn = $columnName;
  }
  else
  {
    $valueObjectProp = '$valueObject->' . lcfirst($columnName);
echo '    if (' . $valueObjectProp . ' != null) $baseObject->set' . ucfirst($columnName) . '(' . $valueObjectProp . ");\n";
  }
}

// if a PK column exists
if ($pkColumn != '')
{
?>

    if ($valueObject-><?php echo lcfirst($pkColumn) ?> == -1)
    {
      $baseObject->setNew(true);
    }
    else
    {
      $baseObject->setNew(false);
      $baseObject->set<?php echo ucfirst($pkColumn) ?>($valueObject->id);
    }
<?php
}
?>

    return $baseObject;
  }

  /**
   * get ValueObject from (Doctrine) BaseObject
   *
   * @param BaseObject The model object
   * @return ValueObject The object to be used remotely
   */   
  protected function toValueObject($baseObject)
  {
    $valueObject = new <?php echo $this->table->getClassnameToReturn() ?>ValueObject();

    if ($baseObject != null)
    {
<?php
$columns = $this->table->getColumns();

foreach ($columns as $columnName => $column)
{
echo '      $valueObject->' . lcfirst($columnName) . ' = $baseObject->get' . ucfirst($columnName) . "();\n";
}
?>
    }

    return $valueObject;
  }
}
