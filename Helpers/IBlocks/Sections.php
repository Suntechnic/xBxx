<?php
namespace Bxx\Helpers\IBlocks;

class Sections
{

    private static $_memoizing = [];

    /**
     * возвращает id инфоблока по его коду
     * \Bxx\Helpers\IBlocks\Sections::getIdByCode('code');
     * @param string $Code - код инфоблока
     * @return int
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    public static function getIdByCode(string $Code, int $IBlockId=0): int
    {

        if (!self::$_memoizing[$IBlockId][$Code]) {
            $dctFilter = array('CODE' => $Code);
            if ($IBlockId > 0) {
                $dctFilter['IBLOCK_ID'] = $IBlockId;
            }
            $lstSections = \Bitrix\Iblock\SectionTable::getList(array(
                    'select' => array('ID'),
                    'filter' => $dctFilter,
                    'cache' => 3599
                ))->fetchAll();
            if (count($lstSections) == 1) {
                self::$_memoizing[$IBlockId][$Code] = $lstSections[0]['ID'];
            } else {
                throw new \Bitrix\Main\ObjectNotFoundException('Section not found');
            }
        }

        return intval(self::$_memoizing[$IBlockId][$Code]);
    }
}