<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "group_product".
 *
 * @property int $id
 * @property string $name
 * @property int $idUser
 * @property string $status
 * @property string $timestamp
 * @property string $receipt_filename
 * @property string $receipt_extension
 *
 * @property GroupCount[] $groupCounts
 */
class GroupProduct extends \yii\db\ActiveRecord
{
    // Определение констант для статусов
    const STATUS_NEW = 'Новая';
    const STATUS_PROCESSING = 'В обработке';
    const STATUS_COMPLETED = 'Выполнено';
    const STATUS_REJECTED = 'Отклонено';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['receipt_filename', 'receipt_extension'], 'string', 'max' => 255],
            [['name', 'idUser', 'status'], 'required'],
            [['idUser'], 'integer'],
            [['status'], 'in', 'range' => [
                self::STATUS_NEW,
                self::STATUS_PROCESSING,
                self::STATUS_COMPLETED,
                self::STATUS_REJECTED,
            ]],
            [['timestamp'], 'safe'],
            [['name'], 'string', 'max' => 60],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название группы',
            'idUser' => 'ID Пользователя',
            'status' => 'Статус',
            'timestamp' => 'Дата создания',
            'receipt_filename' => 'Файл чека',
            'receipt_extension' => 'Расширение файла чека',
        ];
    }

    /**
     * Получает список возможных статусов.
     *
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_NEW => 'Новая',
            self::STATUS_PROCESSING => 'В обработке',
            self::STATUS_COMPLETED => 'Выполнено',
            self::STATUS_REJECTED => 'Отклонено',
        ];
    }

    /**
     * Получает query для [[GroupCounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupCounts()
    {
        return $this->hasMany(GroupCount::className(), ['idGroup' => 'id']);
    }

    /**
     * Устанавливает статус группы продуктов.
     *
     * @param string $status
     * @return bool
     */
    public function setStatus($status)
    {
        if (in_array($status, array_keys(self::getStatusList()))) {
            $this->status = $status;
            return $this->save(false, ['status']);
        }
        return false;
    }

    /**
     * Генерирует квитанцию о покупке.
     *
     * @return bool
     */
    public function generateReceipt()
    {
        try {
            // Проверяем, есть ли уже чек
            if ($this->receipt_filename && $this->receipt_extension) {
                return true; // Чек уже сгенерирован
            }

            // Генерация уникального имени файла
            $randomFilename = Yii::$app->security->generateRandomString(12);

            $receiptDir = Yii::getAlias('@webroot/assets/cart/');
            if (!is_dir($receiptDir)) {
                mkdir($receiptDir, 0777, true);
            }
            $filePath = $receiptDir . $randomFilename . '.png';

            // Проверяем уникальность имени файла
            while (file_exists($filePath)) {
                $randomFilename = Yii::$app->security->generateRandomString(12);
                $filePath = $receiptDir . $randomFilename . '.png';
            }

            // Получаем данные для чека
            $user = $this->user;
            if (!$user) {
                throw new \Exception('Пользователь не найден для заказа ID: ' . $this->id);
            }

            $operationNumber = $this->id;
            $userIdHash = md5($this->idUser);
            $fullName = $user->fio;
            $email = $user->email;
            $operationDate = Yii::$app->formatter->asDatetime($this->timestamp, 'php:d.m.Y H:i:s');

            $orderItems = $this->groupCounts;
            $productDetails = [];
            $totalAmount = 0;
            foreach ($orderItems as $item) {
                $product = $item->idProduct0;
                if (!$product) {
                    continue; // Пропустить, если продукт не найден
                }
                $productName = $product->name;
                $quantity = $item->count;
                $price = $product->price;
                $amount = $price * $quantity;
                $productDetails[] = [
                    'name' => $productName,
                    'quantity' => $quantity,
                    'price' => $price,
                    'amount' => $amount,
                ];
                $totalAmount += $amount;
            }

            // Проверка наличия товаров
            if (empty($productDetails)) {
                throw new \Exception('Нет товаров для генерации чека.');
            }

            // Настройки изображения
            $imageWidth = 600;
            $lineHeight = 20;
            $headerHeight = 100;
            $footerHeight = 70;
            $additionalSpacing = 40; // Дополнительное пространство для отступов
            $itemCount = count($productDetails);
            $imageHeight = $headerHeight + ($itemCount * ($lineHeight + 5)) + $footerHeight + $additionalSpacing;

            // Создаем изображение
            $im = imagecreatetruecolor($imageWidth, $imageHeight);

            // Цвета
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 0, 0, 0);

            // Заливаем фон белым цветом
            imagefilledrectangle($im, 0, 0, $imageWidth - 1, $imageHeight - 1, $white);

            // Путь к шрифту
            $fontPath = Yii::getAlias('@app/fonts/arial.ttf');

            // Проверяем наличие файла шрифта
            if (!file_exists($fontPath)) {
                throw new \Exception('Файл шрифта не найден по пути: ' . $fontPath);
            }

            $fontSizeTitle = 16;
            $fontSizeText = 12;
            $x = 20;
            $y = 30;

            // Добавляем заголовок
            imagettftext($im, $fontSizeTitle, 0, $x, $y, $black, $fontPath, "Квитанция о покупке");
            $y += $lineHeight + 10;

            // Добавляем информацию о заказе
            imagettftext($im, $fontSizeText, 0, $x, $y, $black, $fontPath, "Номер операции: $operationNumber");
            $y += $lineHeight;
            imagettftext($im, $fontSizeText, 0, $x, $y, $black, $fontPath, "ID пользователя: $userIdHash");
            $y += $lineHeight;
            imagettftext($im, $fontSizeText, 0, $x, $y, $black, $fontPath, "ФИО: $fullName");
            $y += $lineHeight;
            imagettftext($im, $fontSizeText, 0, $x, $y, $black, $fontPath, "Email: $email");
            $y += $lineHeight;
            imagettftext($im, $fontSizeText, 0, $x, $y, $black, $fontPath, "Дата операции: $operationDate");
            $y += $lineHeight + 15;

            // Заголовки таблицы товаров
            $columns = ['Наименование', 'Кол-во', 'Цена (руб.)', 'Сумма (руб.)'];
            $columnWidths = [250, 80, 100, 100];
            $currentX = $x;
            foreach ($columns as $index => $col) {
                imagettftext($im, $fontSizeText, 0, $currentX, $y, $black, $fontPath, $col);
                $currentX += $columnWidths[$index];
            }
            $y += $lineHeight - 15;

            // Добавляем линию под заголовками
            imageline($im, $x, $y, $x + array_sum($columnWidths), $y, $black);
            $y += 20;

            // Детали товаров
            foreach ($productDetails as $detail) {
                $currentX = $x;
                // Наименование
                imagettftext($im, $fontSizeText, 0, $currentX, $y, $black, $fontPath, $detail['name']);
                $currentX += $columnWidths[0];
                // Количество
                imagettftext($im, $fontSizeText, 0, $currentX, $y, $black, $fontPath, $detail['quantity']);
                $currentX += $columnWidths[1];
                // Цена с валютой
                $priceWithCurrency = number_format($detail['price'], 2, ',', ' ');
                imagettftext($im, $fontSizeText, 0, $currentX, $y, $black, $fontPath, $priceWithCurrency);
                $currentX += $columnWidths[2];
                // Сумма с валютой
                $amountWithCurrency = number_format($detail['amount'], 2, ',', ' ');
                imagettftext($im, $fontSizeText, 0, $currentX, $y, $black, $fontPath, $amountWithCurrency);
                $y += $lineHeight + 5; // Добавляем дополнительный отступ между строками
            }

            // Добавляем итоговую сумму
            $y += $lineHeight - 25; // Отступ перед итоговой суммой
            $totalText = "Итоговая сумма: " . number_format($totalAmount, 2, ',', ' ');
            imagettftext($im, $fontSizeText, 0, $x, $y, $black, $fontPath, $totalText);

            // Добавляем нижний отступ 45px
            $y += $lineHeight + 45;

            // Сохраняем изображение
            imagepng($im, $filePath);
            imagedestroy($im);

            // Обновляем модель
            $this->receipt_filename = $randomFilename;
            $this->receipt_extension = 'png';
            $this->save(false, ['receipt_filename', 'receipt_extension']);

            return true; // Возвращаем true после успешной генерации
        } finally {

        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (array_key_exists('status', $changedAttributes) && $this->status == self::STATUS_COMPLETED) {
            $this->generateReceipt();
        }
    }
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'idUser']);
    }

}
