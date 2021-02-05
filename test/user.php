<?php
require_once __DIR__.'/../vendor/autoload.php';

class config
{
    public static function getBirthday()
    {
        return rand(1970,2010).'-'.substr('0'.rand(1,12),-2).'-'.substr('0'.rand(1,28),-2);
    }

    public static function lastLoginTime()
    {
        return '16119'.rand(10000,99999);
    }

    public static function lastLoginIp()
    {
        $ip_long = array(
            array('607649792', '608174079'), // 36.56.0.0-36.63.255.255
            array('1038614528', '1039007743'), // 61.232.0.0-61.237.255.255
            array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
            array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
            array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
            array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
            array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
            array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
            array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
            array('-569376768', '-564133889'), // 222.16.0.0-222.95.255.255
        );
        $rand_key = mt_rand(0, 9);
        return $ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    }

    public static function userIcon()
    {
        return '';
    }

    public static function identityCard()
    {
        $city = array(11,12,13,14,15,21,22,23,31,32,33,34,35,36,37,41,42,43,44,45,46,50,51,52,53,54,61,62,63,64,65,71,81,82,91);
        //校验位
        $parity = array('1','0','X','9','8','7','6','5','4','3','2');
//       $a = array('a','b','c');
        $arr = array(0,1,2,3,4,5);
        $str = '';
//       echo $city[array_rand($city)];
        //前两位
        $str .=$city[array_rand($city)];
        //地区位后四位
        for($i=0;$i<4;$i++){
            $str .=$arr[array_rand($arr)];
        }
        //出生年 随机20世纪
        $str .= '19'.mt_rand(0,9).mt_rand(0,9);
        //月份
        $month = array('01','02','03','04','05','06','07','08','09','10','11','12');
        $str .=$month[array_rand($month)];
        //天
        $day = mt_rand(0,3);
        if($day==3){
            $str .=$day.mt_rand(0,1);
        }else{
            $str .=$day.mt_rand(0,9);
        }
        //顺序码
        for($i=0;$i<3;$i++){
            $str .=mt_rand(0,9);
        }
        //计算加权因子
        for($i=18;$i>1;$i--){
            $factor[] = fmod(pow(2,$i-1),11);
        }
        //将加权因子和身份证号对应相乘,再求和
        $sum = 0;
        for($i=0;$i<count($factor);$i++){
            $sum +=$factor[$i]*$str[$i];
        }
        //将sum对11求余
        $mod = fmod($sum,11);
        $str .=$parity[$mod];
        return $str;
    }

    public static function createTime()
    {
        return '161191'.rand(1000,9999);
    }
}

$indexName = 'zzswoole_user';
$indexType = 'user';
$config = [
    'hosts' => [
        '127.0.0.1:9200',
//        '127.0.0.1:9201',
//        '127.0.0.1:9202',
    ]
];

$setIndex = new \zzfufu\ZzElastic\SetIndex();
$setIndex->setIndexName($indexName);
$setIndex->setIndexType($indexType);

$conn = new \zzfufu\ZzElastic\ElasticsearchConnection($config);
$cud = $conn->CUD($setIndex);
//$cud->deleteIndex($indexName);

$pinyin = new Overtrue\Pinyin\Pinyin();
$folderPath = "mingzi/";
$countFile = 0;
$totalFiles = glob($folderPath . "*.txt");
$sexs = ['男', '女'];
foreach ($totalFiles as $filename) {
    $handle = fopen($filename, "r");
    var_dump($handle);
    $datas = [];
    $i = 1;
    while (!feof($handle)) {
        $name = trim(fgets($handle));
        if (empty($name)) continue;
        $datas[] = [
            'user_name' => implode('', $pinyin->convert($name)),
            'real_name' => $name,
            'password' => md5('123456'),
            'sex' => $sexs[rand(0,1)],
            'money' => rand(1000,999999),
            'birthday' => config::getBirthday(),
            'last_login_time' => config::lastLoginTime(),
            'last_login_ip' => config::lastLoginIp(),
            'create_time' => config::createTime()
        ];
        $i++;
//        if ($i % 100 == 0) {
//            echo count($datas),PHP_EOL;
//            $cud->bulk(['body' => $datas]);
//            $datas = [];
//        }
//        $res = $cud->add(['body' => $datas]);
//        var_dump($res);
    }
//var_dump($datas);break;
    echo count($datas),PHP_EOL;
    if (count($datas) <= 0) continue;
    $res = $cud->bulk(['body' => $datas]);
}
