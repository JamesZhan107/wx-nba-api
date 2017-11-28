<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/27
 * Time: 19:55
 */
class Domain_Photo extends PhalApi_Api
{

    function __construct()
    {
        DI()->functions = "Common_Functions";
    }
    /**
     * 图片爬虫列表
     * @param $page
     * @return array
     */
    public function meizi_list($page)
    {
        $rows = DI()->notorm->photo->select('id,img_url,title')->where('p_id', $page)->fetchAll();
        if (empty($rows)) {
            $url = "http://www.meizitu.com/a/more_{$page}.html";
            $res = DI()->functions->HttpGet($url);
            \phpQuery::newDocumentHTML($res);
            $arr = array();
            $list = pq('.wp-list li');
            foreach ($list as $li) {
                $title = pq($li)->find('.tit')->text();
                $img = pq($li)->find('img')->attr('src');
                $url = pq($li)->find('a')->attr('href');
                $id = intval(preg_replace('/\D/s', '', $url));
                $tmp = array(
                    'p_id' => $page,
                    'img_url' => $img,
                    'id' => $id,
                    'title' => $title,
                );
                array_push($arr, $tmp);
                $rs = DI()->notorm->photo->insert($tmp);
            }
            return $arr;
        } else {
            return $rows;
        }
    }

    /**
     * 爬虫获取妹子图片详情列表
     * @param $id
     * @return array
     */
    public function meizi_detail($id)
    {
       // $data = DI()->notorm->photo_detail->where('id', $id)->fetch('list');
        $data = DI()->notorm->photo_detail->select('list')->where('id', $id)->fetch('list');
        $data = json_decode($data, true);
        if (!empty($data)) {
            return $data;
        } else {
            $url = "http://www.meizitu.com/a/{$id}.html";
            $res = DI()->functions->HttpGet($url);
            \phpQuery::newDocumentHTML($res);
            $arr = array();
            $list = pq('#picture p img');
            foreach ($list as $li) {
                array_push($arr, pq($li)->attr('src'));
            }
            $tmp = array(
                'list' => json_encode($arr),
                'id' => $id,
            );
            if (!empty($arr)) {
                $rs = DI()->notorm->photo_detail->insert($tmp);
            }
            return $arr;
        }
    }
}
