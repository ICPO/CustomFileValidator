<?php

namespace app\components;

use yii\base\Component;
use yii\validators\Validator;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;


class CustomFileValidator extends Component
{
    static $attributeToValidate = ['image'];
    static $attributeToIgnore = [];// image_mobile, например. Исключает image_mobile из валидации

    public static function validate(&$model, $options = []): void
    {

        # validate options
        if (count($options) > 0) {
            self::validateOptions($model, $options);
        }

        # получаем поля, которые должны быть валидированы
        $validateList = self::getAttributesWhichNeedValidate($model);

        if (count($validateList) > 0) {
            foreach ($validateList as $item) {

                $instance = UploadedFile::getInstances($model, $item);

                if (count($instance) == 0) {
                    # проверка на update

                    $vls = $model->validators;
                    $validator = Validator::createValidator('required', $model, [$item], ['enableClientValidation' => false]); // отключаем валидацию у клиента, чтобы невзирая на ошибки мог повторно нажать кнопки отправки формы. Валидацию берет на себя скрипт.
                    $vls->append($validator);


                }
            }

        }
    }

    private static function getAttributesWhichNeedValidate($model)
    {
        $tmp = [];
        # получаем поля
        $allAttributes = $model->attributes();

        # формируем игнорируемые для валидациия поля
        $ignoreList = self::createIgnoreList();

        foreach (self::$attributeToValidate as $name) {
            foreach ($allAttributes as $key => $attribute) {
                if (strpos($attribute, $name) !== false) {
                    if (!in_array($attribute, $ignoreList)) {
                        $tmp[] = $attribute;
                    }
                }
            }
        }
        return $tmp;
    }

    private static function createIgnoreList()
    {
        return self::$attributeToIgnore;
    }

    private static function validateOptions($model, $options)
    {
        if (!isset($options['function'])) throw new ServerErrorHttpException('Не передан параметр function в опциях модели [' . $model::className() . ']');

        $call = $options['function']($model);

        if (!isset($call['attributeToValidate'])) throw new ServerErrorHttpException('Не передан результирующий параметр [attributeToValidate] внутри function в опциях модели [' . $model::className() . ']');
        self::$attributeToValidate = $call['attributeToValidate'];

        if (isset($call['attributeToIgnore'])) {
            self::$attributeToIgnore = $call['attributeToIgnore'];
        }

    }
}
