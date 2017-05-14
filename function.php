<?php

require_once 'config.php';

//文字列のタグを無視
function h($str, $flag = ENT_QUOTES, $encoding = "utf-8"){
    return htmlspecialchars($str, $flag, $encoding);
}

//文字列のタグを無視（<br>を改行として扱う）
function hbr($str, $flag = ENT_QUOTES, $encoding = "utf-8"){
    return nl2br(htmlspecialchars($str, $flag, $encoding));
}

//DATETIME型の引数を，FORMAT_DATETIMEで定義した文字列に変換
function datetime_to_str($datetime){
    return date(FORMAT_DATETIME, strtotime($datetime));
}

//ランダムな文字列を生成
function random_str(){
    return sha1(uniqid(rand(), true));
}