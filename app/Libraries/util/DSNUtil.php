<?php

namespace App\Libraries\util;

use PDOException;

/**
 * Class DSNUtil
 * global $db 정보 기반으로 DSN 작성
 * @package App\Libraries\util
 */
class DSNUtil
{
    /**
     * @param $dsnId
     * @return string
     */
    public static function getDSN($dsnId): string
    {
        global $db;
        $DSN = '';

        switch ($db[$dsnId]['driver']) {
            case 'sqlite' :
                $DSN = $db[$dsnId]['driver'] . ':'
                    . $db[$dsnId]['hostname']
                    . $db[$dsnId]['database'];
                break;
            default :
                throw new PDOException('DSNId not defined - DSNId: ' . $dsnId);
                break;
        }

        return $DSN;
    }
}