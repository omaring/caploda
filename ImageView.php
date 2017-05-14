<?php
/*
 * index.phpからデータを受け取り，ImageControllerに渡す
 * show_image($image_id)のように，idを指定するだけで画像を表示できる関数を宣言
 * レイアウトを変更するときはconvert_images_to_html()を変更する
 */

require_once 'config.php';
require_once 'Image.php';
require_once 'ImageController.php';

class ImageView {

    static $instance = null;        //ImageViewインスタンスは1つだけ生成される
    private $image_controller;      //ImageViewインスタンス生成時にImageControllerインスタンスも生成

    /*
     * ImageViewクラスのインスタンスを取得（Singleton）
     */
    public function get_instance() {
        if (!isset($instance)) {
            $instance = new ImageView();
            $instance->image_controller = ImageController::get_instance();
        }
        return $instance;
    }
    /*
     * セッションを用いてユーザのIDを取得
     */
    function get_user_info(){
        $user_id = $this->image_controller->get_user_info();
        return $user_id;
    }
    /*
     * 画像をサーバにアップロードする
     * $image_file : 画像のファイルパス　$_FILES["~"]のように一時的なパスを指定する（必須）
     * $user_id : 画像を投稿したユーザのID（必須）
     * $tag_str : タグを','で区切った文字列（空欄可）
     */
    function upload_image($image_file, $user_id, $tag_str, $image_title) {
        $success = $this->image_controller->upload_image($image_file, $user_id, $tag_str, $image_title);
        return $success;
    }
    /*
     * アップロードの成功/失敗を示すアラートバーを表示
     */
    function show_alert_bar($success){
        if(isset($success)){
            if($success == true){
                echo <<< EOM
                <div class="alert alert-success" role="alert">
                    <strong>success</strong>：　アップロードに成功しました。
                </div>
EOM;
            } else {
                echo <<< EOM
                <div class="alert alert-warning" role="alert">
                    <strong>failed</strong>：　アップロードに失敗しました。
                </div>
EOM;
            }
        }
    }
    /*
     * 全件取得
     * 
     * in:  $sort   ソート順（config.phpで定義）
     */
    function show_image_all($sort){
        //画像をImageインスタンスとして取得
        $images = $this->image_controller->get_image_all($sort);
        //画像をhtmlコードに変換して出力
        echo $this->convert_images_to_html($images);
    }
    /*
     * 画像idを指定して，その画像をhtmlコードとして出力
     * 
     * in:  $image_id   画像ID
     * out: 画像のhtmlコードをechoする
     */
    function show_image($image_id = NULL){
        //画像をImageインスタンスとして取得
        $images = $this->image_controller->get_image($image_id);
        //画像をhtmlコードに変換して出力
        echo $this->convert_images_to_html($images);
    }
    /*
     * ユーザIDを指定し，そのユーザが投稿した画像を表示する
     */
    function show_image_from_user_id($user_id){
        //画像をImageインスタンスとして取得
        $images = $this->image_controller->get_image_from_user($user_id);
        
        echo $this->convert_images_to_html($images);
    }
    /*
     * そのタグがついている画像を取得
     */
    function show_image_from_tag($tag){
        $images = $this->image_controller->get_image_from_tag($tag);
        
        echo $this->convert_images_to_html($images);
    }
    /*
     * タグを取得する
     */
    function show_tag(){
        $tags = $this->image_controller->get_tags();
        echo $this->convert_tags_to_html($tags);
    }
    /*
     * Imageインスタンスをhtmlに変換
     */
    function convert_images_to_html($images){
        $html = "";
        
        foreach($images as $image){
            $img_html = $image->to_html();
            $html .= <<< EOM
            <div class='col-lg-2 col-md-3 col-sm-4 col-xs-6'>
                <div class='thumbnail'>
                    <div class='caption'>
                        {$img_html}
                    </div>
                </div>
            </div>
EOM;
        }
        return $html;
    }
    
    function convert_tags_to_html($tags){
        $html = "";
        foreach($tags as $tag){
            $tag_name = $tag["tag_name"];
            $image_num = $tag["image_num"];
            
            $html .= <<<EOM
            <div>
                <a href='?tag={$tag_name}'>{$tag_name} ({$image_num})</a>
            </div>
EOM;
        }
        return $html;
    }
}
