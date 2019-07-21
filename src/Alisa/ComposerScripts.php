<?php

namespace isamarin\Alisa;

use ZipArchive;

class ComposerScripts
{
    const FILE_NAME = 'dictionary.zip';

    public static function getPhpMorphyLibrary()
    {
        $base_path = __DIR__ . DIRECTORY_SEPARATOR . '../../data/dictionary';
        $archive = file_get_contents('http://sourceforge.net/projects/phpmorphy/files/phpmorphy-dictionaries/0.3.x/ru_RU/morphy-0.3.x-ru_RU-withjo-utf-8.zip/download');
        file_put_contents($base_path . DIRECTORY_SEPARATOR . self::FILE_NAME, $archive);
        unset($archive);
        $zip = new ZipArchive();
        $zip->open($base_path . DIRECTORY_SEPARATOR . self::FILE_NAME);
        $zip->extractTo($base_path);
        unlink($base_path . DIRECTORY_SEPARATOR . self::FILE_NAME);
    }
}
