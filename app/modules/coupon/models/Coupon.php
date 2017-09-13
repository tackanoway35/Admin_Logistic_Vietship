<?php
namespace app\modules\coupon\models;

use Yii;
use yii\helpers\StringHelper;
use app\modules\goidichvu\models\Goidichvu;

class Coupon extends \yii\easyii\components\ActiveRecord
{
    public $dvpt = [];
    public $kv = [];
    public static function tableName()
    {
        return 'coupon';
    }

    public function rules()
    {
        return [
            [['ma_coupon', 'hinh_thuc_khuyen_mai', 'gia_tri', 'gdv_id'], 'required', 'message' => '{attribute} không được để trống'],
            [['dich_vu_phu_troi', 'khu_vuc', 'chi_giam_dich_vu_phu_troi'], 'string'],
            [['gia_tri', 'gioi_han'], 'integer', 'message' => "{attribute} phải là kiểu số"],
        ];
    }

    public function attributeLabels()
    {
        return [
            "ma_coupon" => "Mã coupon",
            'gioi_han' => "Giới hạn số lượt áp dụng",
            'hinh_thuc_khuyen_mai' => "Hình thức khuyến mại",
            'gia_tri' => 'Giá trị áp dụng',
            'gdv_id' => "Gói dịch vụ"
        ];
    }
    
    public function getGoidichvu()
    {
        return $this->hasOne(Goidichvu::className(), ['gdv_id' => 'gdv_id']);
    }
}