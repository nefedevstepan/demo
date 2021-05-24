<?php

namespace app\modules\admin\models;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "request".
 *
 * @property int $id
 * @property string $status
 * @property string|null $name
 * @property string|null $before_img
 * @property string $after_img
 * @property string $why_not
 * @property int $category_id
 * @property string $created_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Category $category
 */
class Request extends \yii\db\ActiveRecord
{
    public $imageFile1;
    public $imageFile2;
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->status = 'Новая';
    }

    /**
     * @return array
     */
    public static function ListStatus(){
        $arr = [
            'Новая' => 'Новая',
            'Решена' => 'Решена',
        ];
        if (Yii::$app->user->can("admin")){
            array_merge($arr,["Отклонена" => "Отклонена"]);
        }
        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'request';
    }

    public function behaviors()
    {
        return[
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                // если вместо метки времени UNIX используется datetime:
                'value' => new Expression('NOW()'),
            ],
            [
            'class' => BlameableBehavior::className(),
            'createdByAttribute' => 'created_by',
            'updatedByAttribute' => 'updated_by',
        ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'name','category_id'], 'required'],
            [['why_not'], 'string'],
            [['category_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at'], 'safe'],
            [['status', 'name', 'before_img', 'after_img'], 'string', 'max' => 255],
            [['imageFile1'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, png, bmp', 'maxSize' => 10 * 1024 * 1024],
            [['imageFile2'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, png, bmp', 'maxSize' => 10 * 1024 * 1024],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],

            ['imageFile2', 'required', 'when' => function($model, $attribute) {
                return $model->status == 'Решена';

            }, 'enableClientValidation' => false],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Статус',
            'name' => 'Название',
            'before_img' => 'Картинка до',
            'after_img' => 'Картинка после',
            'why_not' => 'Почему нет',
            'category_id' => 'Category ID',
            'created_at' => 'Дата создания',
            'created_by' => 'Автор',
            'updated_by' => 'Обновлено',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    public function upload()
    {
        if($this->validate())
        {
            if($this->imageFile1){
                $path1 = 'uploads/' . $this->imageFile1->baseName . '.' . $this->imageFile1->extension;
                $this->imageFile1->saveAs($path1);
                $this->before_img = $path1;

            }
            if($this->imageFile2){
                $path2 = 'uploads/' . $this->imageFile2->baseName . '.' . $this->imageFile2->extension;
                $this->imageFile2->saveAs($path2);
                $this->before_img = $path2;

            }

            return true;
        } else {
            return false;
        }
    }


}
