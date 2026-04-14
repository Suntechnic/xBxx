<?
/*
см. orm в матрасах
*/

namespace Bxx\Abstraction;
{
    abstract class Table extends \Bitrix\Main\Entity\DataManager
    {
        
        const ALLOWED_RECREATE = false;
    
        public function createTable (): bool
        {
            $connection = \Bitrix\Main\Application::getInstance()->getConnection();
            $entity = self::getEntity();
            $tableName = $entity->getDBTableName();
            
            if (!$connection->isTableExists($tableName)) {
                $entity->createDbTable();
            }
            return true;
        }
        
        
        public function dropTable (): bool
        {
            $connection = \Bitrix\Main\Application::getInstance()->getConnection();
            $entity = self::getEntity();
            $tableName = $entity->getDBTableName();
            if ($connection->isTableExists($tableName)) $connection->dropTable($tableName);
            return true;
        }

        private static array|null $refFields = null;
        /**
         * @return array [field_name=>['title'=>string, 'required'=>bool, 'primary'=>bool, 'column_name'=>string, 'data_type'=>string]]
         */
        public static function getFields (): array
        {
            if (!is_array(static::$refFields)) {

                static::$refFields = [];
                foreach (static::getMap() as $field) {
                    static::$refFields[$field->getName()] = [
                            'title'=>$field->getTitle(),
                            'required'=>$field->isRequired(),
                            'primary'=>$field->isPrimary(),
                            'column_name'=>$field->getColumnName(),
                            'data_type'=>$field->getDataType()
                        ];
                }
            }
            
            return static::$refFields;
        }

        /**
         * Возвращает массив названий полей таблицы
         * @return string[]
         */
        public static function getFieldsNames (): array
        {
            return array_keys(static::getFields());
        }

        /**
         * Возвращает массив названий колонок таблицы
         * @return string[]
         */
        public static function getColumnsNames (): array
        {
            return array_map(function ($field) {
                    return $field['column_name'];
                }, static::getFields());
        }

        /**
         * Проверяет является ли массив записью таблицы
         * @param array $refRow
         * @return bool
         */
        public static function isValidRow (array $refRow): bool
        {            
            foreach (static::getFieldsNames() as $NameField) {
                if (!array_key_exists($NameField, $refRow)) return false;
            }
            return true;
        }
    }
}
