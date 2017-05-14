<?php
/*
 * ImageViewからデータを受け取り，データを加工してから，ImageModelに渡す
 * または，受け取ったデータをもとに処理を実行する
 */

require_once 'config.php';
require_once 'function.php';
require_once 'Image.php';
require_once 'ImageModel.php';

class ImageController {

    private static $instance = null;
    private $image_model;
    /*
     * ImageControllerクラスのインスタンスを取得（Singleton）
     */
    public function get_instance() {
        if (!isset($instance)) {
            $instance = new ImageController();
            $instance->image_model = ImageModel::get_instance();
        }
        return $instance;
    }
    function get_image_all($sort){
        $image_instances = [];
        // 画像idが指定されていない場合は全件取得
        $images = $this->image_model->select_image_all($sort);
        foreach ($images as $image){
            $image_id = $image["image_id"];
            $tags = $this->image_model->select_tags_from_image($image_id);
            $image_instances[] =  $this->convert_to_image_instance($image, $tags);
        }
        return $image_instances;
    }
    /*
     * 指定された画像IDをもとに，imagesテーブルからの検索結果を取得し，Imageインスタンスに変換
     * 
     * in: $image_id 画像ID 
     * out: $image_instances[] Imageインスタンスの配列
     */
    function get_image($image_id) {
        $image_instances = [];

        $images = $this->image_model->select_image_all($sort);
        foreach ($images as $image){
            $image_id = $image["image_id"];
            $tags = $this->image_model->select_tags_from_image($image_id);
            $image_instances[] =  $this->convert_to_image_instance($image, $tags);
        }
        return $image_instances;
    }
    /*
     * 指定されたユーザIDをもとに，imagesテーブルからの検索結果を取得し，Imageインスタンスに変換
     * 
     * in: $user_id ユーザID
     * out: $image_instances[] Imageインスタンスの配列
     */
    function get_image_from_user($user_id) {
        $image_instances = [];

        $images = $this->image_model->select_image_from_user_id($user_id);
        foreach ($images as $image){
            $image_id = $image["image_id"];
            $tags = $this->image_model->select_tags_from_image($image_id);
            $image_instances[] =  $this->convert_to_image_instance($image, $tags);
        }
        return $image_instances;
    }
    /*
     * 
     */
    function get_image_from_tag($tag){
        $image_instances = [];
        
        $images = $this->image_model->select_image_from_tag($tag);
        foreach ($images as $image){
            $image_id = $image["image_id"];
            $tags = $this->image_model->select_tags_from_image($image_id);
            $image_instances[] =  $this->convert_to_image_instance($image, $tags);
        }
        return $image_instances;
    }
    /*
     * 連想配列としてタグを取得
     */
    function get_tags(){
        $tags = $this->image_model->select_tag_all();
        return $tags;
    }
    /*
     * SQLで取得した，画像とタグの連想配列を受け取り，Imageインスタンスに変換する
     * 
     * in:  $image  imageテーブルから取得した連想配列
     *      $tags   tagsテーブルから取得した連想配列
     * out: Imageインスタンス
     */
    function convert_to_image_instance($image, $tags){
        $image_id = h($image["image_id"]);
        $image_name = h($image["image_name"]);
        $image_dir = h($image["image_dir"]);
        $user_id = h($image["user_id"]);
        $created = h($image["created"]);
        $tag_names = [];
        foreach ($tags as $tag){
            $tag_names[] = h($tag["tag_name"]);
        }
        return new Image($image_id, $image_name, $image_dir, $user_id, $tag_names, $created);
    }
    /*
     * 画像の情報を受け取り，サーバにアップロードする
     *  - 画像のアップロード
     *  - 画像の情報をimagesテーブルに登録
     *  - タグをtagsテーブルに登録
     * 
     * in:  $image_file 画像ファイル（一時パス）
     *      $user_id    画像を登録したユーザのID
     *      $tag_str    ','で区切られたタグの文字列
     * out: なし
     */
    function upload_image($image_file, $user_id, $tag_str, $image_title) {
        if (!isset($image_file) || !isset($user_id)) {
            return false;
        }
        
        // explode関数は空の文字列に対して変な配列を返してしまうため処理
        $tags = $tag_str == "" ? [] : explode(TAG_DELIMITER, $tag_str);
        //同じタグは1種類まで
        $tags = array_unique($tags);
        
        //画像名が設定されていなければnonameに設定
        if($image_title === ""){
            $image_title = DEFAULT_IMAGE_NAME;
        }
        //ファイル名を"ランダムな文字列.拡張子"にする
        $image_dir = random_str() . "." 
                . pathinfo($image_file['name'], PATHINFO_EXTENSION);
            
        $this->image_model->add_image_transaction($user_id, $tags, $image_title, $image_dir);
        $success = $this->save_image_file($image_file, $image_dir);
        
        return $success;
    }
    /*
     * 画像をサーバに保存
     * in:  $file   画像ファイル（一時パス）
     *      $name   サーバに保存される，拡張子を除いたファイル名（画像をテーブルに登録したときのIDなど
     * out: なし
     */
    function save_image_file($file, $name) {
        if (isset($file)) {
            $tmp_file = $file['tmp_name'];

            if (is_uploaded_file($tmp_file)) {
                if (preg_match("/\.png$|\.jpg$|\.jpeg$|\.bmp$/i", $file['name'])) {
                    $up_dir = DIR_UPLOAD . "/" . $name;

                    if (!move_uploaded_file($tmp_file, $up_dir)) {
//                        echo h("アップロード失敗：" . $name);
                        return false;
                    }
                } else {
//                    echo h("アップロードできるファイルは'png', 'jpg', 'bmp'のみです．");
                    return false;
                }
            }
        }
        return true;
    }
    /*
     * セッションを用いてユーザIDを取得
     *  - ページを訪問したことがなければ，ゲストとしてランダムなユーザ名とパスワードを生成して，usersテーブルに追加
     *  - ページを訪問したことがあれば，ユーザ名とパスワードを参照
     *  - usersテーブルからユーザIDを取得
     * 
     * in:  なし
     * out: $user_id    ユーザID
     */
    function get_user_info(){
        if(!isset($_SESSION["caploda_visited"])){
            $_SESSION["caploda_visited"] = 1;
            $_SESSION["caploda_login"] = true;
            
            $user_name = random_str();
            $password = random_str();
            $_SESSION["caploda_user_name"] = $user_name;
            $_SESSION["caploda_password"] = $password;
            
            // ユーザテーブルにゲストとして追加
            $this->image_model->insert_user($user_name, $password);
        } else {
            $_SESSION["caploda_visited"] += 1;
            $user_name = $_SESSION["caploda_user_name"];
            $password = $_SESSION["caploda_password"];
        }
        $user_id = (int) $this->image_model->select_user_id($user_name, $password)["user_id"];
        return $user_id;
    }
    
}
