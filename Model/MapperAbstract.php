<?php
	
	/**
	 * Provides a centralisation of methods that I found myself writing over
	 * and over again.
	 * 
	 * Makes mapping a Mapper class a bit easier - just implement a few
	 * functions and it'll give you basic Mapper functionality automatically
	 * so extend this because it's ace.
	 * 
	 * Available "easy" functions are:
	 *   - save($obj)
	 *   - find($int, $obj)
	 *   - count()
	 *   - fetchAll($where, $order, $limit, $offset)
	 *   - delete()
	 *
	 * @author hello@jamestitcumb.com
	 * @license GNU GPL v3, see LICENSE for more details
	 */
	abstract class MAL_Model_MapperAbstract
	{
		protected $_dbTable;
		
		/**
		 * Should return the name of the table being used e.g. "Application_Model_DbTable_BlogPost"
		 * @return string
		 */
		abstract protected function getDbTableName();
		
		/**
		 * Should return the name of the object being used e.g. "Application_Model_BlogPost"
		 * @return string
		 */
		abstract protected function getObjectName();
		
		/**
		 * Should return an array of mapped fields to use in the MAL_Model_MapperAbstract::Save function
		 * @param unknown_type $obj
		 */
		abstract protected function getSaveData($obj);
		
		/**
		 * Implement this by setting $obj values (e.g. $obj->setId($row->Id) from a DB row
		 * @param unknown_type $obj
		 * @param Zend_Db_Table_Row_Abstract $row
		 */
		abstract protected function populateObjectFromRow(&$obj, Zend_Db_Table_Row_Abstract $row);
		
		/**
		 * Create an instance of the database table
		 * 
		 * @param string $dbTable You should generate this in MAL_Model_MapperAbstract::getDbTableName()
		 * @throws Exception
		 */
		private function setDbTable($dbTable)
		{
			if (is_string($dbTable))
			{
				$dbTable = new $dbTable();
			}
			
			if (!$dbTable instanceof Zend_Db_Table_Abstract)
			{
				throw new Exception('Invalid table data gateway provided');
			}
			
			$this->_dbTable = $dbTable;
			return $this;
		}

		/**
		 * Return the dbTable object - loads if if not already
		 * @return Zend_Db_Table_Abstract
		 */
		protected function getDbTable()
		{
			if (null === $this->_dbTable)
			{
				$this->setDbTable($this->getDbTableName());
			}
			return $this->_dbTable;
		}
		
		/**
		 * Save an object. Requires MAL_Model_MapperAbstract::getSaveData to be implemented!
		 */
		public function save($obj)
		{
			$data = $this->getSaveData($obj);
			
			if(null === ($id = $obj->getId())) 
			{
				unset($data['id']);
				$this->getDbTable()->insert($data);
			}
			else
			{
				$this->getDbTable()->update($data, array('id = ?' => $id));
			}
		}
		
		/**
		 * Search for an object by it's ID
		 * @param int $id
		 * @param unknown_type $obj
		 */
		public function find($id, $obj)
		{
			$result = $this->getDbTable()->find($id);
			if(0 == count($result))
			{
				return;
			}
			$row = $result->current();
			$this->populateObjectFromRow($obj, $row);
		}
		
		/**
		 * Get the number of rows in this table being mapped
		 */
		public function count()
		{
			$select = $this->getDbTable()->select();
			$sql = $select->from($this->getDbTable(), 'COUNT(*)');
			return $this->getDbTable()->getAdapter()->fetchOne($sql);
		}
		
		/**
		 * Fetch all the rows from a database (optionally with a where, order, limit, offset clause)
		 * 
		 * Requires MAL_Model_MapperAbstract::getObjectName and MAL_Model_MapperAbstract::populateObjectFromRow to be implemented
		 * 
		 * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
		 * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
		 * @param int                               $count  OPTIONAL An SQL LIMIT count.
		 * @param int                               $offset OPTIONAL An SQL LIMIT offset.
		 * @return unknown_type A populated object specified by MAL_Model_MapperAbstract::getObjectName
		 */
		public function fetchAll($where = null, $order = null, $limit = null, $offset = null)
		{
			$resultSet = $this->getDbTable()->fetchAll($where, $order, $limit, $offset);
			
			$obj = $this->getObjectName();
			
			$obj_collection = array();
			foreach ($resultSet as $row)
			{
				$obj_instance = new $obj;
				$this->populateObjectFromRow($obj_instance, $row);
				$obj_collection[] = $obj_instance;
			}
			return $obj_collection;
		}
		
		/**
		 * Delete an object from the database table
		 * @param unknown_type $obj
		 * @return boolean
		 */
		public function delete($obj)
		{
			$id = $obj->getId();
			
			if($id > 0)
			{
				return $this->getDbTable()->delete(array('id = ?' => $id));
			}
			else
			{
				return false;
			}
		}
	}
