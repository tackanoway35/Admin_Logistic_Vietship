<?php
namespace app\modules\donhang\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\widgets\ActiveForm;

use yii\easyii\components\Controller;
use app\modules\donhang\models\Donhang;
use yii\helpers\Json;
use app\modules\giashipnoithanh\models\Giashipnoithanh;
use app\modules\goidichvu\models\Goidichvu;
use yii\easyii\models\Khachhangcoupon;

class AController extends Controller
{
    public function actionIndex()
    {
//        $data = new ActiveDataProvider([
//            'query' => Donhang::find(),
//        ]);
//
//        return $this->render('index', [
//            'data' => $data,
//        ]);
        $result = Donhang::find()->where(['kh_id' => 5])->orderBy(['dh_id' => SORT_DESC])->asArray()->one();
        echo '<pre>';
        echo count($result);
        echo '</pre>';
        exit();
    }

    public function actionCreate()
    {
        $model = new Donhang;
        //Khởi tạo mảng dịch vụ phụ trội
        $model_dvpt = [];
        $model_dvpt['dvpt1'] = [
            'content' => 'Giao hàng mẫu, đổi hàng',
            'key' => 'dvpt1',
            'value' => 0
        ];
        $model_dvpt['dvpt2'] = [
            'content' => 'Hẹn giờ giao, giao sau giờ hành chính',
            'key' => 'dvpt2',
            'value' => 0
        ];
        $model_dvpt['dvpt3'] = [
            'content' => 'Giao bến xe, văn phòng xe',
            'key' => 'dvpt3',
            'value' => 0
        ];
        $model_dvpt['dvpt4'] = [
            'content' => 'Hàng quá khổ',
            'key' => 'dvpt4',
            'value' => 0
        ];

        $model->dvpt = $model_dvpt;
        
                
        if ($model->load(Yii::$app->request->post())) {
            $dataPost = Yii::$app->request->post();

            $kh_id = $dataPost['kh_id'];
            $hinh_thuc_thanh_toan = $dataPost['Donhang']['hinh_thuc_thanh_toan'];
            $tong_tien = $dataPost['Donhang']['tong_tien'];
            $dia_chi_lay_hang = $dataPost['dia_chi_lay_hang'];
            
            $nguoi_nhan_ten = $dataPost['Donhang']['nguoi_nhan_ten'];
            $nguoi_nhan_dia_chi_giao_hang = $dataPost['Donhang']['nguoi_nhan_dia_chi_giao_hang'];
            $nguoi_nhan_so_dien_thoai = $dataPost['Donhang']['nguoi_nhan_so_dien_thoai'];
            
            $san_pham_ten = $dataPost['Donhang']['san_pham_ten'];
            $san_pham_so_luong = $dataPost['Donhang']['san_pham_so_luong'];
            
            //Xử lý mã đơn hàng = kh_id + 4 chữ số bắt đầu từ 1001
            $checkDonHang = Donhang::find()->where(['kh_id' => $kh_id])->orderBy(['dh_id' => SORT_DESC])->asArray()->one();
            
            if(count($checkDonHang) == 0) //Chưa có đơn hàng cho khách hàng này bắt đầu tạo mới từ 1001
            {
                $model->te = 1001;
                $model->ma_don_hang = $kh_id.$model->te;
                
            }
            else if(count($checkDonHang > 0)) //Lấy ra cái te và cộng thêm 1
            {
                $model->te = $checkDonHang['te'] + 1;
                $model->ma_don_hang = $kh_id.$model->te;
            }
            
            if($hinh_thuc_thanh_toan == 'Người gửi thanh toán')
            {
                $model->ma_don_hang .= 'G'.$tong_tien;
            }
            
            //Xử lý người nhận
            $arr_nguoi_nhan = [
                'ten' => $nguoi_nhan_ten,
                'dia_chi_giao_hang' => $nguoi_nhan_dia_chi_giao_hang,
                'so_dien_thoai' => $nguoi_nhan_so_dien_thoai
            ];
            $model->nguoi_nhan = json_encode($arr_nguoi_nhan, JSON_UNESCAPED_UNICODE);
            
            //Xử lý sản phẩm
            $arr_san_pham = [
                'ten' => $san_pham_ten,
                'so_luong' => $san_pham_so_luong
            ];
            $model->san_pham = json_encode($arr_san_pham, JSON_UNESCAPED_UNICODE);
            
             //Xử lý dịch vụ phụ trội
            $model->dich_vu_phu_troi = $dataPost['dvpt'];
            
            //Phạm vi đơn hàng là nội thành
            $model->pham_vi_don_hang = 'nội thành';
            
            //Địa chỉ lấy hàng
            $model->dia_chi_lay_hang = $dia_chi_lay_hang;
            
            //Các trường mặc định
            $model->trang_thai = 'Đã duyệt,chờ lấy';
            $model->kh_id = $kh_id;
            $model->time = time();
            
            if($model->save(false)){
                $result = [
                    'message' => 'success',
                    'data' => [
                        'ma_don_hang' =>  $model->ma_don_hang,
                        'id' => $model->dh_id
                    ]
                ];
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }
            else{
                return [
                    'message' => 'error',
                ];
            }
        }
        else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionSubcat() {    
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $kh_id = $parents[0];
                $data = Donhang::getStock($kh_id);
                $out = [];
                for($i=0; $i<count($data); $i++)
                {
                    $pho = \app\modules\duongpho\models\Duongpho::find()->where(['dp_id' => $data[$i]['dp_id']])->one()['ten_pho'];
                    $out[$i]['id'] = $data[$i]['dp_id'];
                    $out[$i]['name'] = $data[$i]['ten_goi_nho'].' / '.$data[$i]['so_dien_thoai'].' / '.$data[$i]['dia_chi_text'];
                }
                // the getSubCatList function will query the database based on the
                // cat_id and return an array like below:
                // [
                //    ['id'=>'<sub-cat-id-1>', 'name'=>'<sub-cat-name1>'],
                //    ['id'=>'<sub-cat_id_2>', 'name'=>'<sub-cat-name2>']
                // ]
                echo Json::encode(['output'=>$out, 'selected'=>'']);
                return;
            }
        }
        echo Json::encode(['output'=>'', 'selected'=>'']);
    }

    public function actionEdit($id)
    {
        $model = Donhang::findOne($id);
        //Xử lý JSON dich_vu_phu_troi và khu_vuc;
        if($model->dich_vu_phu_troi)
        {
            $arr_dvpt = json_decode($model->dich_vu_phu_troi, true);
        }else
        {
            $arr_dvpt = [];
            $arr_dvpt['dvpt1'] = [
                'content' => 'Giao hàng mẫu, đổi hàng',
                'key' => 'dvpt1',
                'value' => 0
            ];
            $arr_dvpt['dvpt2'] = [
                'content' => 'Hẹn giờ giao, giao sau giờ hành chính',
                'key' => 'dvpt2',
                'value' => 0
            ];
            $arr_dvpt['dvpt3'] = [
                'content' => 'Giao bến xe, văn phòng xe',
                'key' => 'dvpt3',
                'value' => 0
            ];
            $arr_dvpt['dvpt4'] = [
                'content' => 'Hàng quá khổ',
                'key' => 'dvpt4',
                'value' => 0
            ];
        }
        
        if($model->khu_vuc)
        {
            $arr_kv = json_decode($model->khu_vuc, true);
        }else
        {
            $arr_kv = [];
            $khuvuc = Khuvuc::find()->all();
            foreach($khuvuc as $item)
            {
                $arr_kv['kv'.$item->kv_id] = [
                    'content' => $item->ten_khu_vuc,
                    'key' => 'kv'.$item->kv_id,
                    'value' => 0
                ];
            }
        }
        $model->dvpt = $arr_dvpt;
        $model->kv = $arr_kv;
        
        //Xử lý áp dụng KM theo ngày và theo giờ
        if($model->hour_gio_ap_dung) //Áp dụng theo giờ
        {
            $model->thoi_gian_ap_dung = 'hour';
        }else
        {
            $model->thoi_gian_ap_dung = 'day';
        }
        
        //Xử lý áp dụng KM đăng ký mới
        if($model->new_ngay_bat_dau && $model->new_ngay_ket_thuc)
        {
            $model->new_gkh = 1;
        }else
        {
            $model->new_gkh = 0;
        }
        
        if($model === null){
            $this->flash('error', "Không tìm thấy đơn hàng nào");
            return $this->redirect(['/admin/'.$this->module->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            $dataPost = Yii::$app->request->post();
            //Xử lý dịch vụ phụ trội JSON
            if(isset($dataPost['dvpt']))
            {
                foreach($dataPost['dvpt'] as $key => $value)
                {
                    if($arr_dvpt[$key])
                    {
                        $arr_dvpt[$key]['value'] = 1;
                    }
                }
                $model->dich_vu_phu_troi = json_encode($arr_dvpt, JSON_UNESCAPED_UNICODE);
            }else
            {
                $model->dich_vu_phu_troi = NULL;
            }
            
            //Xử lý khu vực JSON
            if(isset($dataPost['kv']))
            {
                foreach($dataPost['kv'] as $key => $value)
                {
                    if($arr_kv[$key])
                    {
                        $arr_kv[$key]['value'] = 1;
                    }
                }
                $model->khu_vuc = json_encode($arr_kv, JSON_UNESCAPED_UNICODE);
            }else
            {
                $model->khu_vuc = NULL;
            }
            
            //Xử lý áp dụng gói KM theo ngày
            if($dataPost[$model->formName()]['thoi_gian_ap_dung'] == 'day')
            {
                $model->day_ngay_bat_dau = strtotime($dataPost['day_gkh_begin']);
                $model->day_ngay_ket_thuc = strtotime($dataPost['day_gkh_end']);
                $model->hour_gio_ap_dung = NULL;
                $model->hour_thoi_gian_ap_dung = NULL;
            }else if($dataPost[$model->formName()]['thoi_gian_ap_dung'] == 'hour')
            {
                $model->hour_thoi_gian_ap_dung = time();
                $model->day_ngay_bat_dau = NULL;
                $model->day_ngay_ket_thuc = NULL;
            }
            
            //Xử lý áp dụng gói KM khi đăng ký mới
            if($dataPost[$model->formName()]['new_gkh'] == 1) //Có áp dụng -> set ngày bắt đầu và kết thúc
            {
                $model->new_ngay_bat_dau = strtotime($dataPost['new_gkh_begin']);
                $model->new_ngay_ket_thuc = strtotime($dataPost['new_gkh_end']);
            }else
            {
                $model->new_ngay_bat_dau = NULL;
                $model->new_ngay_ket_thuc = NULL;
            }
            if(Yii::$app->request->isAjax){
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            else{
                if($model->save()){
                    $this->flash('success', "Cập nhật đơn hàng thành công");
                }
                else{
                    $this->flash('error', "Cập nhật đơn hàng thất bại. Hãy kiểm tra lại thông tin!");
                }
                return $this->refresh();
            }
        }
        else {
            return $this->render('edit', [
                'model' => $model
            ]);
        }
    }

    public function actionDelete($id)
    {
        if(($model = Donhang::findOne($id))){
            $model->delete();
        } else {
            $this->error = "Không tìm thấy đơn hàng nào";
        }
        return $this->formatResponse("Xóa đơn hàng thành công");
    }
    
    //Tính tiền tự động ajax
    public function actionTinhTienTuDong()
    {
        $dataPost = Yii::$app->request->post();        
        $kvg_id = $dataPost['Donhang']['pho_giao_hang'];
        $kvl_id = $dataPost['kvl_id'];
        $gdv_id = $dataPost['Donhang']['gdv_id'];
        $arr_dvpt = json_decode($dataPost['dvpt'], true);
        $cp_id = $dataPost['Donhang']['cp_id'];
        
        $error_message = '';
        $tang_tien_message = '';
        
        $tongtien = Giashipnoithanh::find()->where([
            'kvl_id' => $kvl_id,
            'kvg_id' => $kvg_id,
            'gdv_id' => $gdv_id
        ])->one()['don_gia'];
        
        if(!$tongtien)
        {
            $tongtien = 0;
        }
        
        //Xử lý phần dịch vụ phụ trội
        foreach($arr_dvpt as $key => $value)
        {
            if($value['value'] == 1)
            {
                if($key == 'dvpt1')
                {
                    $tongtien += 10000;
                }
                elseif ($key == 'dvpt2') 
                {
                    $tongtien += 10000;
                }
                elseif ($key == 'dvpt3') 
                {
                    $tongtien += 10000;
                }
                elseif($key == 'dvpt4')
                {
                    //Theo kích thước
                    $dai = $value['note']['dai'];
                    $rong = $value['note']['rong'];
                    $cao = $value['note']['cao'];
                    
                    if($dai == 0 || $rong == 0 || $cao == 0)
                    {
                        $kg_kich_thuoc = 0;
                    }elseif($dai != 0 && $rong != 0 && $cao != 0)
                    {
                        $kg_kich_thuoc = ($dai*$rong*$cao)/6000;
                    }
                    
                    //Theo cân nặng
                    $kg_nang = $value['note']['nang'];
                    if($kg_nang >= $kg_kich_thuoc)
                    {
                        $nang = $kg_nang;
                    }elseif($kg_kich_thuoc >= $kg_nang)
                    {
                        $nang = $kg_kich_thuoc;
                    }
                    
                    if($nang >= 5 && $nang < 10) //Cộng 15000
                    {
                        $tongtien += 15000;
                    }elseif($nang >= 10 && $nang < 25)
                    {
                        $tongtien += 25000;
                    }elseif($nang >= 25 && $nang <40)
                    {
                        $tongtien += 40000;
                    }elseif($nang >= 40 && $nang <60)
                    {
                        $tongtien += 60000;
                    }
                }
            }
        }
        
        //Xử lý tổng tiền khi có mã coupon
        if($dataPost['Donhang']['cp_id'])
        {
            
            $cp_id = $dataPost['Donhang']['cp_id'];
            //Lấy ra các thông tin liên quan về cp_id;
            $cp_details = \app\modules\coupon\models\Coupon::find()->where(['cp_id' => $cp_id])->asArray()->one();
//            echo '<pre>';
//            print_r($cp_details);
//            echo '</pre>';
//            exit();
            $coupon_kh_id = $cp_details['kh_id'];
            $da_su_dung = $cp_details['da_su_dung'];
            $gioi_han = $cp_details['gioi_han'];
            $so_luong_coupon = $cp_details['so_luong_coupon'];
            $hinh_thuc_khuyen_mai = $cp_details['hinh_thuc_khuyen_mai'];
            $gia_tri = $cp_details['gia_tri'];
            $form_kh_id = $dataPost['kh_id'];
//            $ngay_bat_dau = $cp_details['ngay_bat_dau'];
//            $ngay_ket_thuc = $cp_details['ngay_ket_thuc'];
//            $current_time = time();
            
            //Kiểm tra coupon này đã hết hạn sử dụng hay chưa
            //TH1 : Coupon áp dụng riêng cho khách hàng
            if($coupon_kh_id)
            {
                $kh_can = 0; //Biến này dùng để nhận biết những trường hợp nào được áp dụng và không được áp dụng
                //TH1A : trùng khách hàng -> giảm giá
                if($coupon_kh_id == $form_kh_id)
                {
                    $kh_coupon = Khachhangcoupon::find()->where(['cp_id' => $cp_id, 'kh_id' => $coupon_kh_id])->one();
                    if(count($kh_coupon) > 0)
                    {                        
                        $da_su_dung = $kh_coupon['da_su_dung'];
                        if($da_su_dung < $gioi_han)
                        {
                            $kh_can = 1;
                        }
                    }else
                    {
                        $kh_can = 1;
                    }
                    //TH1A-1 : Khách hàng chưa sử dụng hoặc sử dụng ít hơn giới hạn cho phép
                    if($kh_can == 1)
                    {
                        $kv_can = 0;
                        $gdv_can = 0;
                        //Kiểm tra xem khu vực có được áp dụng hay không
                        $arr_kv = json_decode($cp_details['khu_vuc'], true);
                        $kv = [];
                        foreach($arr_kv as $item)
                        {
                            if($item['value'] == 1)
                            {
                                $kv[] = $item['id'];
                            }
                        }

                        $khu_vuc_giao_hang = \app\modules\duongpho\models\Duongpho::find()->where(['dp_id' => $kvg_id])->one()['kv_id'];
                        foreach($kv as $item)
                        {
                            if($item == $khu_vuc_giao_hang)
                            {
                                $kv_can = 1;
                                break;
                            }
                        }

                        //Kiểm tra xem gói dịch vụ có được áp dụng hay không
                        $arr_gdv = json_decode($cp_details['gdv_id'], true);
                        foreach($arr_gdv as $item)
                        {
                            if($item == $gdv_id)
                            {
                                $gdv_can = 1;
                                break;
                            }
                        }

                        if($gdv_can == 0)
                        {
                            $error_message = 'Mã khuyến mại không áp dụng cho gói dịch vụ này';
                        }
                        if($kv_can == 0)
                        {
                            $error_message = 'Mã khuyến mại không áp dụng cho khu vực giao hàng này';
                        }

                        if($gdv_can == 1 && $kv_can == 1) //Thực hiện giảm giá theo coupon
                        {
                            if($hinh_thuc_khuyen_mai == 'Giảm cước')
                            {
                                $tongtien = $tongtien - $gia_tri;
                            }
                            else if($hinh_thuc_khuyen_mai == 'Giảm theo %')
                            {

                                $tongtien = ($tongtien*(100 - $gia_tri))/100;
                            }
                            else if($hinh_thuc_khuyen_mai == 'Đồng giá')
                            {
                                $tongtien = $gia_tri;
                            }
                            else if($hinh_thuc_khuyen_mai == 'Tặng tiền')
                            {
                                $tang_tien_message = 'Bạn được tặng '.$gia_tri.' VNĐ vào tải khoản';
                            }
                        }
                    }
                    //TH1A-2 : Khách hàng sử dụng quá số lần cho phép
                    else
                    {
                        $error_message = "Bạn đã sử dụng quá số lần cho phép";
                    }
                }
                //TH1B : không đúng khách hàng -> báo lỗi
                else
                {
                    $error_message = 'Mã khuyến mại này không áp dụng cho bạn';
                }
            }
            else //TH2 : Coupon áp dụng cho tất cả các khách hàng
            {
                $kv_can = 0;
                $gdv_can = 0;
                //Kiểm tra xem khu vực có được áp dụng hay không
                $arr_kv = json_decode($cp_details['khu_vuc'], true);
                $kv = [];
                foreach($arr_kv as $item)
                {
                    if($item['value'] == 1)
                    {
                        $kv[] = $item['id'];
                    }
                }

                $khu_vuc_giao_hang = \app\modules\duongpho\models\Duongpho::find()->where(['dp_id' => $kvg_id])->one()['kv_id'];
                foreach($kv as $item)
                {
                    if($item == $khu_vuc_giao_hang)
                    {
                        $kv_can = 1;
                        break;
                    }
                }

                //Kiểm tra xem gói dịch vụ có được áp dụng hay không
                $arr_gdv = json_decode($cp_details['gdv_id'], true);
                foreach($arr_gdv as $item)
                {
                    if($item == $gdv_id)
                    {
                        $gdv_can = 1;
                        break;
                    }
                }

                if($gdv_can == 0)
                {
                    $error_message = 'Mã khuyến mại không áp dụng cho khu vực này';
                }
                if($kv_can == 0)
                {
                    $error_message = 'Mã khuyến mại không áp dụng cho khu vực giao hàng này';
                }

                if($gdv_can == 1 && $kv_can == 1) //Thực hiện giảm giá theo coupon
                {
                    if($hinh_thuc_khuyen_mai == 'Giảm cước')
                    {
                        $tongtien = $tongtien - $gia_tri;
                    }
                    else if($hinh_thuc_khuyen_mai == 'Giảm theo %')
                    {

                        $tongtien = ($tongtien*(100 - $gia_tri))/100;
                    }
                    else if($hinh_thuc_khuyen_mai == 'Đồng giá')
                    {
                        $tongtien = $gia_tri;
                    }
                    else if($hinh_thuc_khuyen_mai == 'Tặng tiền')
                    {
                        $tang_tien_message = 'Bạn được tặng '.$gia_tri.' VNĐ vào tải khoản';
                    }
                }
            }
        }
        $result = [
            'tongTien' => $tongtien,
            'couponMessage' => $error_message,
            'tangTienMessage' => $tang_tien_message
        ];
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    
    //Xử lý thông báo giờ lấy hàng và giờ giao hàng ajax thông qua gói dịch vụ
    public function actionThongBaoThoiGianShip()
    {
        $dataPost = Yii::$app->request->post();
        $gdv_id = $dataPost['gdv_id'];
        $gdv = Goidichvu::find()->where(['gdv_id' => $gdv_id])->one()['ten_goi_dich_vu'];
        $currentTimeStamp = time();
        
        $currentHour = (int)date('H', $currentTimeStamp);
        $currentMinute = (int)date('i', $currentTimeStamp);
        $dayOfWeek = (int)date('w', $currentTimeStamp); //0 : Sunday, 6 : Saturday
        
        //Quy đổi hết ra phút và so sánh
        $mocGio = 11*60 + 30;
        $gioHienTai = $currentHour*60 + $currentMinute;
        
        if($gdv == 'Chuyển nhanh')
        {
            if($dayOfWeek != 0) //Không làm ngày chủ nhật
            {
                if($gioHienTai <= $mocGio) //<= 11h30
                {
                    $thoigianship = '-- Thời gian lấy: 8h30 - 12h hôm nay ('.date('d/m/Y', $currentTimeStamp).').<br>';
                    $thoigianship .= '-- Thời gian giao: 13h30 - 17h hôm nay ('.date('d/m/Y', $currentTimeStamp).').<br>.';
                }
                else //>11h30
                {
                    $thoigianship = '-- Thời gian lấy: 13h30 - 17h30 hôm nay ('.date('d/m/Y', $currentTimeStamp).').<br>';
                    if($dayOfWeek == 6) //Lấy vào chiều thứ 7 và giao vào sáng thứ 2 cộng 2 ngày
                    {
                        $ngay_giao = date('d/m/Y', strtotime('+2 days', $currentTimeStamp));
                        $thoigianship .= '-- Thời gian giao: 8h30 - 12h thứ 2 ('.$ngay_giao.').<br>';
                    }else
                    {
                        $ngay_giao = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                        $thoigianship .= '-- Thời gian giao: 8h30 - 12h hôm sau ('.$ngay_giao.').<br>';
                    }
                }
            }
        }
        elseif($gdv == 'Tiết kiệm')
        {
            if($dayOfWeek != 0)
            {
                if($gioHienTai <= $mocGio) //<= 11h30
                {
                    $thoigianship = '-- Thời gian lấy: 8h30 - 17h30 hôm nay ('.date('d/m/Y', $currentTimeStamp).').<br>';
                    if($dayOfWeek == 6) //Thứ 7 lấy vào sáng hôm nay và giao sáng thứ 2
                    {
                        $ngay_giao = date('d/m/Y', strtotime('+2 days', $currentTimeStamp));
                        $thoigianship .= '-- Thời gian giao: 8h30 - 12h thứ 2 ('.$ngay_giao.').<br>';
                    }else
                    {
                        $ngay_giao = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                        $thoigianship .= '-- Thời gian giao: 8h30 - 12h hôm sau ('.$ngay_giao.').<br>';
                    }
                }
                else //>11h30
                {
                    if($dayOfWeek == 6) //Chiều thứ 7 nhận đơn sáng thứ 2 lấy và giao chiều thứ 2
                    {
                        $ngay_lay = date('d/m/Y', strtotime('+2 days', $currentTimeStamp));
                        $ngay_giao = date('d/m/Y', strtotime('+2 days', $currentTimeStamp));
                        $thoigianship = '-- Thời gian lấy: 8h30 - 12 thứ 2 ('.$ngay_lay.').<br>';
                        $thoigianship .= '-- Thời gian giao: 13h30 - 17h30 thứ 2 ('.$ngay_giao.').<br>';
                    }else
                    {
                        $ngay_lay = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                        $ngay_giao = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                        $thoigianship = '-- Thời gian lấy: 8h30 - 12 hôm sau ('.$ngay_lay.').<br>';
                        $thoigianship .= '-- Thời gian giao: 13h30 - 17h30 hôm sau ('.$ngay_giao.').<br>';
                    }
                }
            }
        }
        elseif($gdv == 'Hỏa tốc')
        {
            if($dayOfWeek != 0)
            {
                //8h30 - 11h30 : 480 - 690
                //13h30 - 16h30 : 810 - 990
                //TH1 : $gioHienTai < 480 -> status = 0 (Ngoài giờ hành chính) giao từ 8h - 10h hôm nay
                if($gioHienTai < 480)
                {
                    $ngay_lay_giao = date('d/m/Y', $currentTimeStamp);
                    $thoigianship = '-- Thời gian lấy và giao: 8h - 10h hôm nay ('.$ngay_lay_giao.').<br>';
                }
                //TH2 : 690 - 810 -> status = 1 (Ngoài giờ hành chính) giao 13h30 - 15h30 hôm nay
                elseif($gioHienTai > 690 && $gioHienTai < 810)
                {
                    $ngay_lay_giao = date('d/m/Y', $currentTimeStamp);
                    $thoigianship = '-- Thời gian lấy và giao: 13h30 - 15h30 hôm nay ('.$ngay_lay_giao.').<br>';
                }
                //TH3 : >990 -> status = 2 (Ngoài giờ hành chính) giao 8h - 10h hôm sau
                elseif($gioHienTai > 990)
                {
                    $ngay_lay_giao = date('d/m/Y', strtotime('+1 days', $currentTimeStamp));
                    $thoigianship = '-- Thời gian lấy và giao: 8h - 10h hôm sau ('.$ngay_lay_giao.').<br>';
                }
                //TH4 : 480 <=  <= 690 -> status = 3 (Trong giờ hành chính buổi sáng) giao sau 2 tiếng
                elseif(($gioHienTai >= 480 && $gioHienTai <= 690) || ($gioHienTai >= 810 && $gioHienTai <= 990))
                {
                    $ngay_lay_giao = date('d/m/Y', $currentTimeStamp);
                    $lay = $currentHour.'h'.$currentMinute;
                    $giao = ($currentHour+2).'h'.$currentMinute;
                    $strLayGiao = $lay.' - '.$giao;
                    $thoigianship = '-- Thời gian lấy và giao: '.$strLayGiao.' hôm nay ('.$ngay_lay_giao.').<br>';
                }
            }
        }
        
        return $thoigianship;
    }
    
    //Function print order
    public function actionPrint($id)
    {
        $model = Donhang::findOne($id);
        return $this->renderPartial('print', ['model' => $model]);
    }
}