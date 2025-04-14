<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

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
 * @property int $price
 *
 * @property GroupCount[] $groupCounts
 * @property Category $category
 * @property User $idUser0
 * @property GroupProduct $idGroup0
 */
class Product extends \yii\db\ActiveRecord
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
            [['name', 'description', 'idUser', 'idCategory', 'count'], 'required','message'=>'Поле должно быть заполнено!'],
            [['description'], 'string'],
            [['timestamp'], 'safe'],
            [['idUser', 'idCategory', 'count', 'price'], 'integer'],
            [['name'], 'string', 'max' => 60,'message'=>'Поле должно быть не меньше 60 символов'],
            [['photo'], 'string', 'max' => 255,'message'=>'Поле должно быть не меньше 255 символов'],
            [['idCategory'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['idCategory' => 'id']],
            [['idUser'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['idUser' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'description' => 'Описание',
            'timestamp' => 'Время размещения',
            'idUser' => 'Имя модератора',
            'idCategory' => 'Категория',
            'photo' => 'Фотокарточка товара',
            'count' => 'Количество',
            'price' => 'Цена',
        ];
    }

    /**
     * Gets query for [[GroupCounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupCounts()
    {
        return $this->hasMany(GroupCount::className(), ['idProduct' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'idCategory']);
    }

    /**
     * Gets query for [[IdUser0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdUser0()
    {
        return $this->hasOne(User::className(), ['id' => 'idUser']);
    }

    /**
     * Получает полный URL к изображению товара.
     *
     * @return string
     */
    public function getPhotoUrl()
    {
        return Url::to('@web/uploads/' . $this->photo);
    }

    // Если у вас есть отношение с GroupProduct, добавьте его здесь
    // public function getGroupProduct()
    // {
    //     return $this->hasOne(GroupProduct::className(), ['id' => 'idGroup']);
    // }
}
