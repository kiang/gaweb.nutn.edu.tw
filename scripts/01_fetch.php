<?php
$basePath = dirname(__DIR__);
$pageRawPath = $basePath . '/raw/page';
if(!file_exists($pageRawPath)) {
    mkdir($pageRawPath, 0777, true);
}
$detailRawPath = $basePath . '/raw/detail';

$baseUrlPage = 'http://gaweb.nutn.edu.tw/Reparation/Post.asp?Page=';
$baseUrlDetail = 'http://gaweb.nutn.edu.tw/Reparation/Content.asp?serial=';
$totalPage = 1;
$currentPage = 1;
while($currentPage <= $totalPage) {
    $pageRawFile = $pageRawPath . '/' . $currentPage . '.html';
    if(!file_exists($pageRawFile)) {
        $pageRaw = mb_convert_encoding(file_get_contents($baseUrlPage . $currentPage), 'utf-8', 'big5');
        file_put_contents($pageRawFile, $pageRaw);
    } else {
        $pageRaw = file_get_contents($pageRawFile);
    }

    if($totalPage === 1) {
        $pos = strpos($pageRaw, '頁數：');
        $posEnd = strpos($pageRaw, '<img', $pos);
        $parts = explode('：', substr($pageRaw, $pos, $posEnd - $pos));
        $totalPage = intval($parts[1]);
    }

    $lines = explode('</tr>', $pageRaw);
    foreach($lines AS $line) {
        $partBegin = strrpos($line, '<tr ');
        $line = substr($line, $partBegin);
        $line = str_replace(['-->', '&nbsp;', '<!--'], '', $line);
        $cols = explode('</td>', $line);
        if(count($cols) === 13 && false !== strpos($cols[0], '-')) {
            foreach($cols AS $k => $v) {
                $cols[$k] = trim(strip_tags($v));
            }
            $dateParts = explode('/', $cols[8]);
            $yearPath = $detailRawPath . '/' . $dateParts[0];
            if(!file_exists($yearPath)) {
                mkdir($yearPath, 0777, true);
            }
            $detailRawFile = $yearPath . '/' . $cols[0] . '.html';
            if(!file_exists($detailRawFile)) {
                file_put_contents($detailRawFile, mb_convert_encoding(file_get_contents($baseUrlDetail . $cols[0]), 'utf-8', 'big5'));
            }
            $detailRaw = file_get_contents($detailRawFile);
            $pos = strpos($detailRaw, '請修事項<br>');
            $pos = strpos($detailRaw, '<td', $pos);
            $posEnd = strpos($detailRaw, '</td>', $pos);
            $content = trim(strip_tags(substr($detailRaw, $pos, $posEnd - $pos)));
            if(false !== strpos($content, '水')) {
                $cols[6] = $content;
                print_r($cols);
            }
        }
    }

    ++$currentPage;
}