<?
namespace Bxx;

use \Bitrix\Main\Application;

abstract class Table extends \Bitrix\Main\Entity\DataManager
{
    
    const ALLOWED_RECREATE = false;

    public function createTable () {
        $connection = Application::getInstance()->getConnection();
        $entity = self::getEntity();
        $tableName = $entity->getDBTableName();
        
        if (!$connection->isTableExists($tableName)) {
            $entity->createDbTable();
        }
        return true;
    }
    
    
    public function dropTable () {
		$connection = Application::getInstance()->getConnection();
        $entity = self::getEntity();
        $tableName = $entity->getDBTableName();
		if ($connection->isTableExists($tableName)) $connection->dropTable($tableName);
        return true;
    }
	
}