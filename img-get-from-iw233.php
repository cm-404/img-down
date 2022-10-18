<?php
/**
 * @ProjectName: iw233 全api图片批量下载
 * @Description: 基于http://api.iw233.cn/API/index.php   图片链接随机获取，不是设置了100就一定是下载100张图片，可能当前获取的图片中部分是已下载过的
 * @Author: cm-404
 * @GitHub: https://github.com/cm-404/img-down.git
 * @CreateDate: 2022/10/18 15:39
 * @Version: 1.0
 */


/**
 * curl get
 * @param $url
 * @return bool|string
 */
function curl_get($url)
{
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($curl);
  curl_close($curl);
  return $output;
}

/**
 * 自动下载
 * @param string $sort api图片类型
 * @param int $num 当前下载图片张数，最大值100
 * @return int
 */
function down_img_by_api($sort, $num = 100)
{
  $count = 0;

  //图片网站图片类型汇总
  $_arr_api_sort = [
    ['sort' => 'random', 'text' => '随机壁纸（全部图）'],
    ['sort' => 'iw233', 'text' => '随机壁纸（无色图）'],
    ['sort' => 'top', 'text' => '壁纸推荐'],
    ['sort' => 'yin', 'text' => '银发'],
    ['sort' => 'cat', 'text' => '兽耳'],
    ['sort' => 'xing', 'text' => '星空'],
    ['sort' => 'mp', 'text' => '竖屏壁纸'],
    ['sort' => 'pc', 'text' => '横屏壁纸']
  ];

  # api接口汇总
  //$_api_host = 'https://iw233.cn/api.php';      # 60秒120次GET,超过拉黑IP
  $_api_host = 'https://api.iw233.cn/api.php';  # 并发连接：100，超过404
  //$_api_host = 'https://ap1.iw233.cn/api.php';  # 并发连接：100，超过404
  //$_api_host = 'https://dev.iw233.cn/api.php';  # 并发连接：80， 超过404

  echo '<br>api_host:' . $_api_host . '<br>';

  $_info_sort = [];
  foreach ($_arr_api_sort as $_sort) {
    if ($_sort['sort'] == $sort) {
      $_info_sort = $_sort;
      break;
    }
  }

  if ($_info_sort) {
    $_url_api = $_api_host . '?sort=' . $_info_sort['sort'] . '&num=' . $num . '&type=json';

    $_str_json = curl_get($_url_api);
    $_info_pic = json_decode($_str_json, true);

    if ($_info_pic) {
      $_list_pic_url = $_info_pic['pic'];
      $dirname = './img-down-temp/' . $_info_sort['text'];//检查文件夹是否存在，不存在则创建

      if (is_dir($dirname) || @mkdir($dirname)) {

        foreach ($_list_pic_url as $_url_pic) {
          $_name_pic = basename($_url_pic);//获取图片文件名
          $fullName = $dirname . '/' . $_name_pic;
          if (!file_exists($fullName)) {

            #region func 1
            /*$content = file_get_contents($url);
            file_put_contents($fullName, $content);*/
            #endregion

            #region func 2
            $in = fopen($_url_pic, "rb");
            $out = fopen($fullName, "wb");
            while ($chunk = fread($in, 8192)) {
              fwrite($out, $chunk, 8192);
            }
            fclose($in);
            fclose($out);
            #endregion

            $count++;
          }
        }
      }
    }
  }

  return $count;
}


echo '开始下载时间：' . date('Y-m-d H:i:s');
$_count_img = down_img_by_api('pc');
echo '<br>完成下载时间：' . date('Y-m-d H:i:s');
echo '<br>合计下载图片' . $_count_img . '张';

