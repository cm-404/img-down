<?php
/*
 * +----------------------------------------------------------------------
 * |  图片盲盒自动下载
 * |  基于http://api.iw233.cn/API/index.php
 * |  https://iw233.cn/API/Random.php 色图盲盒，0.05%概率出露点瑟图(每4000张稳定井一张)
 * +----------------------------------------------------------------------
 */

//获取实际图片连接
function get_redirect_url_by_header($url)
{
  $header = get_headers($url, 1);
  if (strpos($header[0], '301') !== false || strpos($header[0], '302') !== false) {
    if (is_array($header['Location'])) {
      return $header['Location'][count($header['Location']) - 1];
    } else {
      return $header['Location'];
    }
  } else {
    return $url;
  }
}

//保存图片
function down_img_by_url($url, $name, $dir = '')
{
  if (empty($dir)) $dir = date('Y-m-d');
  $dirname = './img-down-temp/' . $dir;//检查文件夹是否存在，不存在则创建

  if (is_dir($dirname) || @mkdir($dirname)) {
    $fullName = './img-down-temp/' . $dir . '/' . $name;
    if (!file_exists($fullName)) {

      #region func 1
      /*$content = file_get_contents($url);
      file_put_contents($fullName, $content);*/
      #endregion

      #region func 2
      $in = fopen($url, "rb");
      $out = fopen($fullName, "wb");
      while ($chunk = fread($in, 8192)) {
        fwrite($out, $chunk, 8192);
      }
      fclose($in);
      fclose($out);
      #endregion

      return true;
    }
  }

  return false;
}

//自动保存
/**
 * @param int $_count_max 设置本次最大下载量
 * @return int
 */
function down_img_count($_count_max = 50)
{
  $_count = 0;
  while ($_count < $_count_max) {
    $_url_api = 'https://iw233.cn/API/Random.php';
    $_url_pic = get_redirect_url_by_header($_url_api);//获取图片完整连接
    $_name_pic = basename($_url_pic);//获取图片文件名
    down_img_by_url($_url_pic, $_name_pic, 'iw233');//保存图片至本地文件夹
    $_count++;
    sleep(0.7);//设置请求延时,防止拉黑ip
  }

  return $_count;
}

echo '开始下载时间：' . date('Y-m-d H:i:s');
$_count_img = down_img_count();
echo '<br>完成下载时间：' . date('Y-m-d H:i:s');
echo '<br>合计下载图片' . $_count_img . '张';

