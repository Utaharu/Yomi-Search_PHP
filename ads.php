<?php
/**
 * ヘッダ、フッタ等広告表示
 */

/**
 * header ad
 */
function printHeaderAd($no=0)
{
    $HEADER_ADS_ARRAY = array();
    
    //=============================//
    //ヘッダー部分の広告タグ設定
    //=============================//
    $HEADER_ADS_ARRAY[0] = <<<EOF
EOF;

    if(isset($HEADER_ADS_ARRAY[$no]) && $HEADER_ADS_ARRAY[$no] != '') echo $HEADER_ADS_ARRAY[$no];
}


/**
 * サイト一覧表示時に右に表示する広告があればTRUEを返します
 * なければFALSEを返します
 *
 * @return boolean TRUE 広告あり　FALSE 広告なし
 */
function getRightAdFlg()
{
    return FALSE;
}


/**
 * サイト一覧表示時に右に表示する広告の設定
 */
function printRightAd($no=0)
{
    $RIGHT_ADS_ARRAY = array();
    
    //=============================//
    //右部分の広告タグ設定
    //=============================//
    $RIGHT_ADS_ARRAY[0] = <<<EOF
EOF;

    if(isset($RIGHT_ADS_ARRAY[$no]) && $RIGHT_ADS_ARRAY[$no] != '') echo $RIGHT_ADS_ARRAY[$no];
 }



/**
 * footer ad
 */
function printFooterAd($no=0)
{
    $FOOTER_ADS_ARRAY = array();
    //=============================//
    //フッター部分の広告タグ設定
    //=============================//
    $FOOTER_ADS_ARRAY[0] = <<<EOF
EOF;

    if(isset($FOOTER_ADS_ARRAY[$no]) && $FOOTER_ADS_ARRAY[$no] != '') echo $FOOTER_ADS_ARRAY[$no];
}




?>