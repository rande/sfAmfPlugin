[?php

/**
 * <?php echo $this->table->getClassname() ?> service base class.
 *
 * @package    ##PROJECT_NAME##
 * @author     ##AUTHOR_NAME##
 */
abstract class Base<?php echo $this->table->getClassname() ?>Service extends sfAmfService
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
    catch(PropelException $e)
    {
      return false;
    }
  }
  
  /**
   * create a record in database from ValueObject
   *
   * @param int Limit of ValueObject to return, 0 being no limit (default: 0)
   * @return Array of ValueObject
   * @AmfClassMapping(name="<?php echo $this->getFullVoPackage() ?>.<?php echo $this->table->getClassname(); ?>ValueObject")
   * @AmfReturnType("ArrayCollection")
   */   
  public function retrieveAll($limit = 0)
  {
    $valueObjects = array();
    $baseObjects = <?php echo $this->table->getClassname() ?>Peer::doSelect(new Criteria());

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
   * @AmfClassMapping(name="<?php echo $this->getFullVoPackage() ?>.<?php echo $this->table->getClassname(); ?>ValueObject")
   */   
  public function retrieveById($id)
  {
    return $this->toValueObject(<?php echo $this->table->getClassname() ?>Peer::retrieveByPK($id));
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
  public function fromValueObject($valueObject)
  {
    $baseObject = new <?php echo $this->table->getClassname() ?>();
    
<?php
// set baseObject fields via setter based on valueObject fields
// PK is ignored as it need a custom action.
$pkColumn = '';
$columns = $this->table->getColumns();

foreach ($columns as $column)
{
  if ($column->isPrimaryKey())
  {
    $pkColumn = $column;
  }
  else
  {
    $columnPhpName = $column->getPhpName();
    $valueObjectProp = '$valueObject->' . lcfirst($columnPhpName);
echo '    if (' . $valueObjectProp . ' != null) $baseObject->set' . $columnPhpName . '(' . $valueObjectProp . ");\n";
  }
}

// if a PK column exists
if ($pkColumn != '')
{
?>
    if ($valueObject-><?php echo lcfirst($pkColumn->getPhpName()) ?> == -1)
    {
      $baseObject->setNew(true);
    }
    else
    {
      $baseObject->setNew(false);
      $baseObject->set<?php echo $pkColumn->getPhpName() ?>($valueObject->id);
    }
<?php
}
?>

    return $baseObject;
  }

  /**
   * get ValueObject from (Propel) BaseObject
   *
   * @param BaseObject The model object
   * @return ValueObject The object to be used remotely
   */   
  public function toValueObject($baseObject)
  {
    $valueObject = new <?php echo $this->table->getClassname() ?>ValueObject();

    if ($baseObject != null)
    {
<?php
$columns = $this->table->getColumns();

foreach ($columns as $column)
{
$columnPhpName = $column->getPhpName();
echo '      $valueObject->' . lcfirst($columnPhpName) . ' = $baseObject->get' . $columnPhpName . "();\n";
}
?>
    }

    return $valueObject;
  }
}
