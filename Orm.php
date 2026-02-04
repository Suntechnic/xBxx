<?
namespace Bxx;

use \Bitrix\Main\Application;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\IO;

/**
 * вставка в инит:
if (class_exists('\Bxx\Orm')) {
	\Bxx\Orm::getInstance()->checkVersion();
}
 */

class Orm
{
    
    private static $instances;
        
    public static function getInstance(string $TablesDir='/local/php_interface/lib/App/Tables') {
        if (!isset(self::$instances[$TablesDir])) {
            self::$instances[$TablesDir]= new self($TablesDir);
        }
        return self::$instances[$TablesDir];
    }
    
    
    private $tablesDir;
    private $TDPathHash;
    protected function __construct($TablesDir) {
        $FullPath = \Bitrix\Main\Application::getDocumentRoot().$TablesDir;
        $this->tablesDir = new IO\Directory($FullPath);
        if (!$this->tablesDir->isExists()) {
            throw new \Bitrix\Main\ObjectNotFoundException('Папка классов таблиц '.$FullPath.' не существует');
        }
        $this->TDPathHash = crc32($FullPath);
    }

    /*
     * возвращает папку таблиц
    */
    public function getTablesDir ()
    {
        return $this->tablesDir;
    }
    
    
    
    public function checkVersion () {
        //Option::set('.bxx', 'orm_version', 0);
        $OrmDbHash = Option::get('.bxx', 'orm_hash_'.$this->TDPathHash, '');
        $OrmAppHash = $this->getHash();
        if ($OrmDbHash && $OrmAppHash && $OrmDbHash == $OrmAppHash) return true;
        
        
        // получем классы и версии
        $lstTablesClasses = $this->getTablesClasses();
        $refTablesVersions = $this->refTablesVersions();
        
        foreach ($lstTablesClasses as $className) {
            $entity = $className::getEntity();
            $tableName = $entity->getDBTableName();
            
            // получаем текущую версию таблиц
            $orm_db_tv = Option::get('.bxx', 'orm_tv_'.$this->TDPathHash.'_'.$tableName, '0');
            $orm_app_tv = $refTablesVersions[$tableName];

            if ($orm_db_tv != $orm_app_tv) {
                
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                // START: пересоздание таблицы
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                
                $isSuccess = false;
                
                $connection = Application::getInstance()->getConnection();
                if ($connection->isTableExists($tableName)) {
                    
                    try {
                        // пробуем пересоздать таблицу с сохранением данных
                        
                        $tmp_tableName = 'app_tmptable_'.$tableName;
                        
                        // переименовываем существующую таблицу
                        $r = $connection->query('RENAME TABLE '.$tableName.' TO '.$tmp_tableName);
                        
                        // создаем таблицу
                        $r = $entity->createDbTable();
                        
                        // переносим данные из временной таблицы в новую
                        $fields = '`'.implode('`, `',array_keys($connection->getTableFields($tmp_tableName))).'`';
                        $sql = 'INSERT INTO '.$tableName.' ('.$fields.') SELECT '.$fields.' FROM '.$tmp_tableName.';';
                        $r = $connection->query($sql);
                        
                        // удаляем временную таблицу
                        $r = $connection->query('DROP TABLE '.$tmp_tableName);
                        
                        // все отлично - переходим к следующей таблице
                        $isSuccess = true;
                        
                    } catch (\Exception $e) {
                        
                        //\Kint::dump('Ошибка обнавления таблицы', ['table' => $tableName, 'error'=>$e]);
                        
                        
                        // если есть временная таблица (т.е. текущую таблицу переименовали)
                        if ($connection->isTableExists($tmp_tableName)) {
                            
                            if ($connection->isTableExists($tableName)) { // если успели создать новую
                                $connection->query('DROP TABLE '.$tableName); // выбросим ее
                            }
                            // вернем временную
                            $connection->query('RENAME TABLE '.$tmp_tableName.' TO '.$tableName);
                        }
                        // тут востановили все как было
                        
                        
                        if ($className::ALLOWED_RECREATE === true) { // разрешено пересоздание при обновлении
                            $connection->dropTable($tableName);
                            $entity->createDbTable();
                            
                            //\Kint::dump('Таблица пересоздана, так как это разрешено', ['table' => $tableName]);
                        } else {
                            throw new \Bitrix\Main\DB\Exception('Unable to update table version for '.$tableName);
                            // return false;
                        }
                    }
                    
                } else { // если таблицы нет - ее просто достаточно создать
                    $entity->createDbTable();
                }
                
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                // END: пересоздание таблицы
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                
                
                // тут все более-менее хорошо - сохраняем версию в бд
                Option::set('.bxx', 'orm_tv_'.$this->TDPathHash.'_'.$tableName, $orm_app_tv);
                
            }
        }
        
        // если мы вышли из цикла - можно обновить хэш в БД
        Option::set('.bxx', 'orm_hash_'.$this->TDPathHash, $OrmAppHash);
    }
    /*
    * 
    * Обновляет таблицу сущности
    * 
    */
    // public static function updateTable ($entity)
    // {
    //     $tableName = $entity->getDBTableName();
    // }
    
    
    
	/*
     * возвращает имена классов таблиц в заданном порядке
     * 0 => string (20) "\App\Tables\BidTable"
     * 1 => string (23) "\App\Tables\BidderTable"
	*/
    private $lstTablesClasses;
    public function getTablesClasses (): array
    {
        
        if (!$this->lstTablesClasses) {
            $dir = $this->getTablesDir();
            $lstTablesClasses = [];

            foreach ($dir->getChildren() as $child) {
                if ($child->isFile()) {
                    $fileName = $child->getName();
                    $debrisFileName = explode('.',$fileName);
                    
                    if (count($debrisFileName) == 2 && $debrisFileName[1] == 'php') {
                        $className = '\\App\\Tables\\'.$debrisFileName[0];
                        $lstTablesClasses[] = $className;
                    }
                    
                }
               
            }
            
            sort($lstTablesClasses);
            $this->lstTablesClasses = $lstTablesClasses;
        }
        
        
        return $this->lstTablesClasses;
        
    }
    
    /*
     * возвращает версии таблиц в заданном порядке, соответствюущем порядку классов
     * b_app_tables_bid => string (3) "1.0"
     * b_app_tables_bidder => string (3) "1.0"
    */
    private $refTablesVersions;
    public function refTablesVersions (): array
    {
        if (!$this->refTablesVersions) {
            $lstTablesClasses = $this->getTablesClasses();
            $refTablesVersions = [];
            foreach ($lstTablesClasses as $className) {
                $entity = $className::getEntity();
                $tableName = $entity->getDBTableName();
                $refTablesVersions[$entity->getDBTableName()] = $className::VERSION;
            }
            $this->refTablesVersions = $refTablesVersions;
        }
        return $this->refTablesVersions;
    }
    
    /*
     * возвращает хеш версию БД приложения
    */
    public function getHash () {
        $dir = $this->getTablesDir();
        $DirPath = $dir->getPath();

        $hashFile = new IO\File($DirPath.'/hash');
        $scriptDumpFile = new IO\File($DirPath.'/dump.sh');
        
        if ($hashFile->isExists()) {
            $Hash = $hashFile->getContents();
            if ($Hash) return $Hash;
        }
        
        // хэша орма приложения еще нет
        $refTablesVersions = $this->refTablesVersions();

        $Hash = md5(serialize($refTablesVersions));
        $hashFile->putContents($Hash);

        // $connection = \Bitrix\Main\Application::getConnection();
        // $Cmd = 'mysqldump -u '.$connection->getLogin().' -p'.$connection->getPassword().' '.$connection->getDBName().' '.implode(' ',array_keys($refTablesVersions)).' > '.$DirPath.'/dump.sql';
        // $scriptDumpFile->putContents("#!/bin/bash\n".$Cmd);
        
        return $hash;
        
    }
}