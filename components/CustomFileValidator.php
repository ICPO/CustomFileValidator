<?php

namespace app\components;

use yii\base\Component;
use yii\validators\Validator;
use yii\web\UploadedFile;


class CustomFileValidator extends Component
{

    static $systemName = ['image'];
    static $systemDelimiter = '_';
    static $systemIgnore = [];// mobile, например. Исключает image_mobile из валидации

    public static function validate(&$model): void
    {
        # получаем поля, которые должны быть валидированы
        $validateList = self::getAttributesWhichNeedValidate($model);

        if (count($validateList) > 0) {
            foreach ($validateList as $item) {

                $instance = UploadedFile::getInstances($model, $item);

                if (count($instance) == 0) {
                    $vls = $model->validators;
                    $validator = Validator::createValidator('required', $model, [$item]);
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

        foreach (self::$systemName as $name) {
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
        $tmp = [];
        foreach (self::$systemName as $name) {
            foreach (self::$systemIgnore as $ignore) {
                if (!in_array($name . self::$systemDelimiter . $ignore, $tmp)) {
                    $tmp[] = $name . self::$systemDelimiter . $ignore;
                }
            }
        }
        return $tmp;
    }
}
