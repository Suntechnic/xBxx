<?
/*
см. orm в матрасах
*/

namespace Bxx\Abstraction;
{
    abstract class Table extends \Bitrix\Main\Entity\DataManager
    {
        
        const ALLOWED_RECREATE = false;
    
        public function createTable () {
            $connection = \Bitrix\Main\Application::getInstance()->getConnection();
            $entity = self::getEntity();
            $tableName = $entity->getDBTableName();
            
            if (!$connection->isTableExists($tableName)) {
                $entity->createDbTable();
            }
            return true;
        }
        
        
        public function dropTable () {
            $connection = \Bitrix\Main\Application::getInstance()->getConnection();
            $entity = self::getEntity();
            $tableName = $entity->getDBTableName();
            if ($connection->isTableExists($tableName)) $connection->dropTable($tableName);
            return true;
        }

        
    }
}
