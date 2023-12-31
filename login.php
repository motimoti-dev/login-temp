<?
//ログイン常態かを保存している
//初期は非ログイン
$login = false;

//ログイン常態とか全体のステータス、今はログインに関することしか出力しない
$status = "初期状態";

$login_page_file_name = 'login.php';

//ユーザーデータを入れるやつ
$userdata = [
    "username"=>post_value_check("user"),
    "password"=>post_value_check("password"),
];

//送信されたデータに指定されたパラメータがなければfalseを返し、あればその値を出力するやつ
function post_value_check($key){
    if(isset($_POST[$key])){
        return $_POST[$key];
    }else{
        return false;
    }
}

if(isset($_POST["login"])){
    $status = "ログインフォームが送信された場合 |";
    if($userdata["username"])
    //userの値があるかチェックする
    {
        $status = "ログインフォームが送信されjsonは有効、userの値も存在する";
        if(file_exists("./data/".$_POST["user"].".json"))
        //userが存在するかをチェックする
        {
            $status = "ログインフォームが送信されuserの値も存在し、該当ユーザーも存在";

            //ユーザーデータ名のjsonファイルを探して存在したらそこのパスワードと一致するか見る
            $json = file_get_contents("./data/".$_POST["user"].".json");
            $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
            $json = json_decode($json,true);

            if($userdata["password"])
            //パスワードが入力されているかチェック
            {
                $status = "ログインフォームが送信されuserの値も存在し、該当ユーザーも存在。パスワードも送信されている";
                if($_POST["password"] == $json["password"]){
                    $status = "ログインフォームに必要な情報はすべて送信されログインユーザーのパスワードの照合も完了";
                    $login = true;
                }else{
                    $status = "ログインフォームに必要な情報はすべて送信されているがパスワードが間違っている";
                }
            }else{
                $status = "ログインフォームが送信されuserの値も存在し、該当ユーザーも存在。しかしパスワードが未送信";
            }
        }else{
            $status = "ログインフォームが送信されuserの値も存在するが該当ユーザーは存在しない";
        }
    }
}elseif(isset($_POST["logout"]))
//ログアウトボタンが押された場合
{
    $status = "ログアウトボタンが押された場合";
    // ログアウト（セッションデータを削除）する
    session_start();
    unset($_SESSION["user_id"]);
    header('Location: ./'.$login_page_file_name);
    exit;
}else{
    $status = "ログインフォームが送信されていない場合(すでにログイン中の場合、ログインに成功しリダイレクトされた場合も含む)";
}

//セッション処理
session_start();
if($login){// 入力されたIDとパスワードに一致するユーザーが存在する場合
    $status = "ログインに成功しセッション開始している状態";
    // セッションにユーザーIDを保存しておく
    $_SESSION ["user_id"] = $userdata["username"];

    // ログイン中？（セッションにユーザーIDがある？）
    if (array_key_exists("user_id", $_SESSION)) {
        //header関数を使用し、リダイレクトされる、リロードでフォームをどうするか聞かれないために
        header('Location: ./'.$login_page_file_name);
        exit;
    }else{// ログアウト中？（セッションにユーザーIDがない？） 
        $login = false;
    }
}else{
    if(isset($_SESSION ["user_id"])){//そもそもセッションにuser idがあるか
        if(file_exists("./data/".$_SESSION ["user_id"].".json")){//セッションがあり存在するユーザーの場合ログイン状態に変更する
            $status = "【ログイン中】".$status;
            $login = true;
            //個別ユーザーのjson取得
            $json = file_get_contents("./data/".$_SESSION ["user_id"].".json");
            $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
            $json = json_decode($json,true);
            // ログイン中ユーザーなのでユーザーデータを$userdataにセットする
            $userdata["username"] = $_SESSION ["user_id"];
            $userdata["usernickname"] = $json["nickname"];
        }else{
            $login = false;
        }
    }else{
        $status = "【未ログイン】".$status;
        $login = false;
    }
}

//ログインステータスを吐き出す
echo $status;
if(!$login){
    ?>
    <form class='time-manage' method="post" action="./<?=$login_page_file_name?>">
        <h2>ログイン</h2>
        <div>
            <label>
                <span>user</span>
                <input type='text' name='user' placeholder="ユーザー名" autocomplete='"username' required>
            </label>
            <label>
                <span>pass</span>
                <input type='password' name='password' placeholder="パスワード" required>
            </label>
            <input type='hidden' name='login'>
            <input type="submit" value='ログイン' id='login'>
        </div>
    </form>
<?}else{?>
    <p><?=$userdata["usernickname"]?>としてログイン中</p>
    <form class='time-manage' method="post" action="./<?=$login_page_file_name?>">
        <p><?=$userdata["usernickname"]?>としてログイン中です！</p>
        <input type='hidden' name='logout'>
        <input type="submit" value='ログアウトする' id='logout'>
    </form>
<?}?>