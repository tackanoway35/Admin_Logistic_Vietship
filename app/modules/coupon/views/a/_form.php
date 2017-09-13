<?php
use kartik\date\DatePicker;
use kartik\field\FieldRange;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\khuvuc\models\Khuvuc;
use app\modules\goidichvu\models\Goidichvu;
use kartik\checkbox\CheckboxX;
use richardfan\widget\JSRegister;

$module = $this->context->module->id;
?>
<div class="row">
    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data', 'class' => 'model-form', 'id' => 'myForm']
    ]); ?>
    <div class="col-md-12 col-xs-12 col-sm-12">
        <div class="panel">
            <header class="panel-heading">
                Thông tin coupon
                <span class="tools pull-right">
                    <a class="collapse-box fa fa-chevron-down" href="javascript:;"></a>
                </span>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'ma_coupon')?>
                    </div>
                    <div class="col-md-6">
                        <?=
                            $form->field($model, 'gdv_id')->widget(Select2::className(), [
                                'data' => ArrayHelper::map(Goidichvu::find()->all(), 'gdv_id', 'ten_goi_dich_vu'),
                                'options' => [
                                    'placeholder' => 'Chọn gói dịch vụ áp dụng'
                                ]
                            ])
                        ?>                        
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <?=
                            $form->field($model, 'hinh_thuc_khuyen_mai')->widget(Select2::className(), [
                                'data' => [
                                    'Giảm theo %' => 'Giảm theo %',
                                    'Giảm cước' => 'Giảm cước',
                                    'Đồng giá' => 'Đồng giá',
                                    'Tặng tiền' => 'Tặng tiền'
                                ],
                                'options' => [
                                    'placeholder' => "Chọn hình thức khuyến mại"
                                ]
                            ])
                        ?>
                    </div>
                    
                    <div class="col-md-4">
                        <?= $form->field($model, 'gia_tri')?>
                    </div>
                    
                    <div class="col-md-4">
                        <?= $form->field($model, 'gioi_han')?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            $dateRanger = <<< HTML
                                <span class="input-group-addon">Từ ngày</span>
                                {input1}
                                <span class="input-group-addon">Đến ngày</span>
                                {input2}
                                <span class="input-group-addon kv-date-remove">
                                    <i class="glyphicon glyphicon-remove"></i>
                                </span>
HTML;
                            
                                if($model->ngay_bat_dau && $model->ngay_ket_thuc)
                                {
                                    $beginTime = date('d-m-Y', $model->ngay_bat_dau);
                                    $endTime = date('d-m-Y', $model->ngay_ket_thuc);
                                    echo '<label>Thời gian áp dụng</label>';
                                    echo DatePicker::widget([
                                        'type' => DatePicker::TYPE_RANGE,
                                        'name' => 'ngay_bat_dau',
                                        'value' => $beginTime,
                                        'name2' => 'ngay_ket_thuc',
                                        'value2' => $endTime,
                                        'separator' => '<i class="glyphicon glyphicon-resize-horizontal"></i>',
                                        'layout' => $dateRanger,
                                        'pluginOptions' => [
                                            'autoclose' => true,
                                            'format' => 'dd-M-yyyy'
                                        ]
                                    ]);
                                }else
                                {
                                    echo '<label>Thời gian áp dụng</label>';
                                    echo DatePicker::widget([
                                        'type' => DatePicker::TYPE_RANGE,
                                        'name' => 'ngay_bat_dau',
                                        'value' => date('d-m-Y'),
                                        'name2' => 'ngay_ket_thuc',
                                        'value2' => date('d-m-Y'),
                                        'separator' => '<i class="glyphicon glyphicon-resize-horizontal"></i>',
                                        'layout' => $dateRanger,
                                        'pluginOptions' => [
                                            'autoclose' => true,
                                            'format' => 'dd-M-yyyy'
                                        ]
                                    ]);
                                }
                        ?>
                    </div>
                </div>
        
                <div class="row" style="margin-top: 20px">
                    <div class="col-md-6">
                        <?php
                            if($model->chi_giam_dich_vu_phu_troi)
                            {
                                echo $form->field($model, 'chi_giam_dich_vu_phu_troi')->widget(CheckboxX::classname(), [
                                    'autoLabel'=>true,
                                    'value' => $model->chi_giam_dich_vu_phu_troi,
                                    'pluginOptions' => [ 'threeState' => false ]
                                ])->label(false);
                            }else
                            {
                                echo $form->field($model, 'chi_giam_dich_vu_phu_troi')->widget(CheckboxX::classname(), [
                                    'autoLabel'=>true,
                                    'pluginOptions'=>['threeState'=>false]
                                ])->label(false);
                            }
                        ?>
                        <legend><small>Dịch vụ phụ trội</small></legend>
                        <?php foreach($model->dvpt as $item):?>
                            <div class="form-group has-success">
                                <label class="cbx-label" for="<?= $item['key']?>">
                                <?= CheckboxX::widget([
                                    'name' => 'dvpt['.$item['key'].']',
                                    'value' => $item['value'],
                                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                    'options'=>['id' => $item['key']],
                                    'pluginOptions' => [
                                        'theme' => 'krajee-flatblue',
                                        'enclosedLabel' => true,
                                        'threeState' => false
                                    ]
                                ]); ?>
                                <?= $item['content']?>
                                </label>
                            </div>
                        <?php endforeach;?>
                    </div>
                    
                    <div class="col-md-6">
                        
                        <p>Lựa chọn khu vực để giới hạn áp dụng gói khách hàng</p>
                        
                        <legend><small>Khu vực</small></legend>
                        <?php foreach($model->kv as $item):?>
                            <div class="form-group">
                            <label class="cbx-label" for="<?= $item['key']?>">
                            <?= CheckboxX::widget([
                                'name' => 'kv['.$item['key'].']',
                                'value' => $item['value'],
                                'initInputType' => CheckboxX::INPUT_CHECKBOX,
                                'options'=>['id' => $item['key']],
                                'pluginOptions' => [
                                    'theme' => 'krajee-flatblue',
                                    'enclosedLabel' => true,
                                    'threeState' => false
                                ]
                            ]); ?>
                            <?= $item['content'];?>
                            </label>
                        </div>
                        <?php endforeach;?>
                    </div>
                </div>
                
                    <?= Html::submitButton("Lưu mã khuyến mại", ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end();?>
    
<?php JSRegister::begin();?>
<script>
    var thoi_gian_ap_dung_checked = $('input[name="Goikhachhang[thoi_gian_ap_dung]"]:checked', '#myForm').val();
    if(thoi_gian_ap_dung_checked == 'day') //Show date ranger and hide Hour number
    {
        $("#hour_gkh_number").css({'display' : 'none'});
        $("#day_gkh_date_ranger").css({'display' : 'block'});
    }else if(thoi_gian_ap_dung_checked == 'hour') //Hide date ranger and show hour number
    {
        $("#hour_gkh_number").css({'display' : 'block'});
        $("#day_gkh_date_ranger").css({'display' : 'none'});
    }
    
    $('#myForm input[name="Goikhachhang[thoi_gian_ap_dung]"]').change(() => {
        var thoi_gian_ap_dung_checked = $('input[name="Goikhachhang[thoi_gian_ap_dung]"]:checked', '#myForm').val();
        if(thoi_gian_ap_dung_checked == 'day') //Show date ranger and hide Hour number
        {
            $("#hour_gkh_number").css({'display' : 'none'});
            $("#day_gkh_date_ranger").css({'display' : 'block'});
        }else if(thoi_gian_ap_dung_checked == 'hour') //Hide date ranger and show hour number
        {
            $("#hour_gkh_number").css({'display' : 'block'});
            $("#day_gkh_date_ranger").css({'display' : 'none'});
        }
    })
    
    var new_gkh_checked = $('#new_gkh').val();
    if(new_gkh_checked == 1)
    {
        $("#new_gkh_date_ranger").css({'display' : 'block'});
    }else if(new_gkh_checked == 0)
    {
        $("#new_gkh_date_ranger").css({'display' : 'none'});
    }
    $("#new_gkh").change(() => {
        var new_gkh_value = $("#new_gkh").val();
        if(new_gkh_value == 1)
        {
            $("#new_gkh_date_ranger").css({'display' : 'block'});
        }else if(new_gkh_value == 0)
        {
            $("#new_gkh_date_ranger").css({'display' : 'none'});
        }
    });
</script>
<?php JSRegister::end();?>