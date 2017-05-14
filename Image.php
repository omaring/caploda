<?php
/*
 *  画像のオブジェクト
 *  to_html()によってhtmlコードに変換できる
 */
require_once 'config.php';
require_once 'function.php';

class Image {

    private $image_id;
    private $image_name;
    private $image_dir;
    private $user_id;
    private $tag_names = [];
    private $viewed = 0;
    private $created;
    private $favorites = [];
    
    
    function __construct($image_id, $image_name, $image_dir, $user_id, $tag_names, $created) {
        $this->image_id = h($image_id);
        $this->image_name = h($image_name);
        $this->image_dir = h($image_dir);
        $this->user_id = h($user_id);
        $this->tag_names = $tag_names;
        $this->created = isset($created) ? h($created) : new DateTime(); 
    }

    function get_image_id() {
        return $this->image_id;
    }

    function get_image_name() {
        return $this->image_name;
    }

    function get_user_id() {
        return $this->user_id;
    }
    
    function get_tag(){
        return $this->tags;
    }

    function get_viewed() {
        return $this->viewed;
    }

    function get_created() {
        return $this->created;
    }
    
    /*
     * インスタンスが持っている情報からhtmlコードを返す
     * 
     * in:  なし
     * out: htmlコードを文字列として返す
     */
    function to_html() {
        // 画像の表示に必要な変数を用意
        $image_name = h($this->image_name);
        $image_dir = DIR_UPLOAD . "/" . h($this->image_dir);
        $created = h(date(FORMAT_DATETIME, strtotime($this->created)));
        // タグ列のhtmlコードを生成して$tags_htmlに格納
        $tags_html = "";
        foreach($this->tag_names as $tag){
            $tags_html .= "<span><a href='tag.php?tag=" . h($tag) . "'>" . h($tag) . "</a></span>";
            $tags_html .= "   ";
        }
        
        // htmlコード
        $images_html = <<<EOM
            <div class='non-overflow'>
                <a href="{$image_dir}"><img src="{$image_dir}" width="auto" alt="{$image_name}"></a>
                <a href="{$image_dir}"><h4>{$image_name}</h4></a>
                <p>{$created}</p>
                {$tags_html}
            </div>
EOM;
            return $images_html;
    }
}
