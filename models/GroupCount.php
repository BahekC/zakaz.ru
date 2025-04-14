<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "group_count".
 *
 * @property int $id
 * @property int $idGroup
 * @property int $idProduct
 * @property int $idUser
 * @property int $count
 *
 * @property GroupProduct $idGroup0
 * @property Product $idProduct0
 */
class GroupCount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_count';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idGroup', 'idProduct', 'idUser', 'count'], 'required'],
            [['idGroup', 'idProduct', 'idUser', 'count'], 'integer'],
            [['idGroup'], 'exist', 'skipOnError' => true, 'targetClass' => GroupProduct::className(), 'targetAttribute' => ['idGroup' => 'id']],
            [['idProduct'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['idProduct' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idGroup' => 'ID Группы',
            'idProduct' => 'ID Товара',
            'idUser' => 'ID Пользователя',
            'count' => 'Количество',
        ];
    }

    /**
     * Получает query для [[GroupProduct]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdGroup0()
    {
        return $this->hasOne(GroupProduct::className(), ['id' => 'idGroup']);
    }

    /**
     * Получает query для [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdProduct0()
    {
        return $this->hasOne(Product::className(), ['id' => 'idProduct']);
    }
}
