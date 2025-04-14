<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $timestamp
 * @property int $idUser
 * @property int $idCategory
 * @property int $idGroup
 * @property string|null $photo
 * @property int $count
 *
 * @property GroupCount[] $groupCounts
 * @property Category $idCategory0
 * @property User $idUser0
 * @property GroupProduct $idGroup0
 */
class ProductCreateForm extends Product
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'idUser', 'idCategory', 'count','price'], 'required','message'=>'Поле должно быть заполнено!'],
            [['description'], 'string'],
            [['timestamp'], 'safe'],
            [['idUser', 'idCategory', 'count','price'], 'integer'],
            [['name'], 'string', 'max' => 60,'message'=>'Поле должно быть не меньше 60 символов'],
            [['photo'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg, bmp', 'maxSize'=>10*1024*1024,'message'=>'Файл должен быть формата png, jpg, jpeg, bmp и не больше 10мб!'],
            [['idCategory'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['idCategory' => 'id']],
            [['idUser'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['idUser' => 'id']],
        ];
    }


}
//[['photo'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg, bmp', 'maxSize'=>10*1024*1024,'message'=>'Файл должен быть формата png, jpg, jpeg, bmp и не больше 10мб!'],